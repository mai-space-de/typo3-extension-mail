# Features — EXT:mai_mail

`mai_mail` is the sole email-dispatch extension for the `www.bgm-pulheim.org` TYPO3 project.
No other extension may send email directly or declare a `symfony/mailer` dependency.
All outbound email passes through `MailService::queue()` and is dispatched asynchronously.

---

## 1 · Mail Queue

Outbound emails are stored in `tx_maimail_queue` before dispatch.  
Any extension that needs to send email injects `MailService` and calls:

```php
$this->mailService->queue(
    recipient: 'user@example.com',
    subject:   'Your confirmation',
    htmlBody:  $renderedHtml,
    scheduledAt: null,          // null = send as soon as the next queue run fires
);
```

`scheduledAt` accepts any `DateTimeInterface` for deferred delivery; when `null` it defaults to the current timestamp so the email is picked up on the next queue run.

### Queue status lifecycle

```
                      ┌────────────────────────────────┐
                      │            QUEUED               │◄─────────────────────────┐
                      │  status = 'queued'              │                          │
                      │  retry_count = 0                │                          │
                      └──────────────┬─────────────────┘                          │
                                     │  mail:process-queue selects                │
                                     │  scheduled_at ≤ now()                      │
                                     ▼                                            │
                      ┌────────────────────────────────┐                          │
                      │          PROCESSING             │                          │
                      │  status = 'processing'          │                          │
                      └───────┬────────────────┬────────┘                         │
                              │                │                                  │
                    send OK   │                │  send fails                      │
                              │                │  retry_count < 3                 │
                              ▼                ▼                                  │
              ┌───────────────┐   ┌────────────────────────┐                      │
              │     SENT      │   │  back to QUEUED        │──────────────────────┘
              │ status='sent' │   │  retry_count += 1      │  (retried on next run)
              └───────────────┘   └────────────────────────┘
                                          │
                                          │  send fails
                                          │  retry_count >= 3
                                          ▼
                              ┌────────────────────────┐
                              │        FAILED          │
                              │  status = 'failed'     │
                              │  error_message = …     │
                              └────────────────────────┘
```

| Status | Meaning |
|---|---|
| `queued` | Waiting for the next `mail:process-queue` run |
| `processing` | Currently being dispatched (transient — normally milliseconds) |
| `sent` | Successfully delivered to the MTA |
| `failed` | All 3 dispatch attempts exhausted; manual intervention required |

---

## 2 · Retry Strategy

`MailService::dispatch()` applies automatic retry handling:

- On each transport failure the `retry_count` column is incremented by 1.
- If `retry_count < 3` the status is reset to `queued` so the email is retried on the next
  queue run.
- Once `retry_count >= 3` (i.e. the 3rd consecutive failure) the status is set to `failed`
  and the email will no longer be picked up automatically.
- Every failed dispatch attempt — regardless of whether it is retried or not — writes a row
  to `tx_maimail_log` with `status = 'failed'` and the full exception message.

Failed entries can be manually re-queued from the backend module (see section 5), which
resets `status = 'queued'`, `retry_count = 0`, and `error_message = ''`.

---

## 3 · Queue Worker (CLI Command)

The `mail:process-queue` Symfony console command drives dispatch:

```bash
# Run via TYPO3 console (DDEV)
ddev exec vendor/bin/typo3 mail:process-queue

# Or register in TYPO3 Scheduler as a recurring task
```

**What the command does:**

1. Queries `tx_maimail_queue` for rows where `status = 'queued'` AND `scheduled_at ≤ now()`.
2. Processes each row in ascending `scheduled_at` order (oldest scheduled first).
3. Delegates each row to `MailService::dispatch()`.
4. Reports `Processed N mail(s), M failure(s).` on completion.
5. Returns `Command::SUCCESS` when all processed; `Command::FAILURE` when at least one fails.

The recommended scheduler cadence is every 5 minutes. A future-dated `scheduledAt` value
allows campaigns or reminders to be queued ahead of time and delivered at the right moment.

---

## 4 · Mail Log

Every dispatch attempt (success or failure) writes a row to `tx_maimail_log`.
The log is append-only — rows are never updated or deleted by the system.

Log entries are surfaced in the backend module (see section 5) and can be used for:

- Delivery auditing
- Debug of transient failures and their error messages
- Identifying recipients whose emails consistently fail

---

## 5 · Backend Module

The **Mai Mail** backend module (accessible under *Web* in the TYPO3 backend) provides:

| Action | Description |
|---|---|
| **Queue overview** | Lists all entries in `tx_maimail_queue` ordered by creation date descending |
| **Log overview** | Shows the 50 most recent entries from `tx_maimail_log` |
| **Resend** | Resets a queue entry to `status = 'queued'`, `retry_count = 0`, `error_message = ''` |
| **Delete** | Removes an entry from `tx_maimail_queue` (irrecoverable) |

---

## 6 · Site-based Configuration (TypoScript)

The sender identity is configured per site via TypoScript constants:

```typoscript
plugin.tx_maimail {
    settings {
        defaultSenderName  = BGM Pulheim
        defaultSenderEmail = noreply@bgm-pulheim.org
        headerTemplate     =   # path to a site-specific header partial (optional)
        footerTemplate     =   # path to a site-specific footer partial (optional)
    }
}
```

If `defaultSenderEmail` is empty or the TypoScript setting is missing, `MailService` falls back
to `$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']` (the TYPO3 system default).

---

## 7 · Database Tables

### `tx_maimail_queue`

| Column | Type | Description |
|---|---|---|
| `uid` | `int` | Auto-increment primary key |
| `pid` | `int` | Always `0` (system record) |
| `tstamp` | `int` | Unix timestamp of last update |
| `crdate` | `int` | Unix timestamp of creation |
| `subject` | `varchar(255)` | Email subject line |
| `recipient` | `varchar(255)` | Recipient email address |
| `body` | `mediumtext` | Full HTML email body |
| `status` | `varchar(20)` | `queued` / `processing` / `sent` / `failed` |
| `retry_count` | `int unsigned` | Number of failed dispatch attempts (0–3) |
| `error_message` | `text` | Last exception message; empty on success |
| `scheduled_at` | `int unsigned` | Unix timestamp of earliest dispatch time |
| `sent_at` | `int unsigned` | Unix timestamp when status became `sent`; `0` until then |

### `tx_maimail_log`

| Column | Type | Description |
|---|---|---|
| `uid` | `int` | Auto-increment primary key |
| `pid` | `int` | Always `0` (system record) |
| `tstamp` | `int` | Unix timestamp of log write |
| `crdate` | `int` | Unix timestamp of log write |
| `subject` | `varchar(255)` | Email subject line |
| `recipient` | `varchar(255)` | Recipient email address |
| `status` | `varchar(20)` | `sent` or `failed` |
| `sent_at` | `int unsigned` | Unix timestamp of the dispatch attempt |
| `error_message` | `text` | Exception message on failure; empty on success |

---

## 8 · Integration Guide

Other extensions that need to send email must:

1. Declare `maispace/mai-mail` as a Composer dependency.
2. Inject `Maispace\MaiMail\Service\MailService` via constructor autowiring.
3. Call `MailService::queue()` — never instantiate `MailMessage` or use `Mailer` directly.

```php
use Maispace\MaiMail\Service\MailService;

final class MyService
{
    public function __construct(
        private readonly MailService $mailService,
    ) {}

    public function notifyUser(string $email, string $subject, string $html): void
    {
        $this->mailService->queue(
            recipient: $email,
            subject:   $subject,
            htmlBody:  $html,
        );
        // Returns void — the email is queued; dispatch happens asynchronously.
    }
}
```

Extensions that need deferred delivery (e.g. a reminder at a future date):

```php
$this->mailService->queue(
    recipient:   $user->getEmail(),
    subject:     'Your appointment reminder',
    htmlBody:    $html,
    scheduledAt: new \DateTimeImmutable('2026-06-01 08:00:00'),
);
```

The email will not be dispatched until the first `mail:process-queue` run on or after the
scheduled timestamp.

---

## 9 · Architecture Constraints

- `mai_mail` is the **sole** extension that may declare `symfony/mailer` as a dependency.
- No other extension may instantiate `MailMessage` or call `Mailer::send()` directly.
- The `tx_mainewsletter_subscriber` table (owned by `mai_newsletter`) is entirely separate
  from the mail queue — `mai_mail` has no knowledge of subscribers.
- `mai_mail` is a transactional mail engine. Marketing/bulk email is owned by `mai_newsletter`.
