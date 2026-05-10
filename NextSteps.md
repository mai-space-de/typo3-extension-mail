# NextSteps — EXT:mai_mail

Current state: scaffold + skeleton implementation.
All files exist and PHP syntax is valid. The extension is **not yet functional** — several gaps
must be resolved before it can be installed and used.

---

## 1 · Backend controller — implement resend & delete actions

**File:** `Classes/Controller/Backend/MailBackendController.php`

Both `resendAction()` and `deleteAction()` contain `// TODO` comments and do nothing useful.

**resendAction** must:
1. Read the `uid` argument from the request (`(int)$this->request->getArgument('uid')`).
2. Reset `status = 'queued'`, `retry_count = 0`, `error_message = ''` via `ConnectionPool`.
3. Flash success and redirect to `index`.

**deleteAction** must:
1. Read the `uid` argument from the request.
2. Delete the row from `tx_maimail_queue` via `ConnectionPool`.
3. Flash success and redirect to `index`.

Note: `createModuleTemplate()` in `AbstractBackendController` takes no arguments — the current
call is correct as-is.

---

## 2 · Repositories — hydrate domain models

**Files:** `Classes/Domain/Repository/MailQueueRepository.php`,
           `Classes/Domain/Repository/MailLogRepository.php`

Both repositories are fully implemented with real `QueryBuilder` queries. The missing piece is
that they return `array[]` while the domain models are typed value objects that are never
instantiated. Complete the hydration:

```php
// In MailQueueRepository::findAll() and findByStatus():
return array_map(
    static fn(array $row) => new MailQueue(
        uid: (int)$row['uid'],
        subject: $row['subject'],
        recipient: $row['recipient'],
        body: $row['body'],
        status: $row['status'],
        retryCount: (int)$row['retry_count'],
        scheduledAt: (int)$row['scheduled_at'],
        sentAt: (int)$row['sent_at'],
    ),
    $rows
);
```

Apply the same pattern to `MailLogRepository::findRecent()` using `MailLog`.

---

## 3 · Domain models — constructor signature

**Files:** `Classes/Domain/Model/MailQueue.php`, `Classes/Domain/Model/MailLog.php`

The models are correct typed value objects (immutable, `final`, no Extbase inheritance).
Option B (typed models) is already chosen — no rethinking needed.

Verify that `MailLog`'s constructor matches the columns in `tx_maimail_log` (subject, recipient,
status, sent_at, error_message). Add `errorMessage` if it is missing.

---

## 4 · Fluid templates — fix property accessor names

**Files:** `Resources/Private/Partials/Backend/Mail/QueueTable.html`,
           `Resources/Private/Partials/Backend/Mail/LogTable.html`

Fluid resolves object properties via getters. With typed models the template must use
**camelCase** accessor names, not the snake_case DB column names:

| Template currently | Must become |
|---|---|
| `{mail.retry_count}` | `{mail.retryCount}` |
| `{mail.scheduled_at}` | `{mail.scheduledAt}` |
| `{mail.sent_at}` | `{mail.sentAt}` |
| `{mail.error_message}` | `{mail.errorMessage}` |

Also:
- Replace `f:uri.action` links with `f:link.action` for correct backend module routing in
  TYPO3 14.
- Add a `data-confirm="Are you sure?"` attribute (or a `<f:if>` confirmation pattern) on the
  Delete button to prevent accidental deletion.

---

## 5 · MailService — add sender configuration

**File:** `Classes/Service/MailService.php`

`MailMessage` is instantiated in `ProcessMailQueueCommand` but sender name/address are never
set. The TypoScript constants `plugin.tx_maimail.settings.defaultSenderName` and
`plugin.tx_maimail.settings.defaultSenderEmail` are defined but never consumed.

Inject `\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface` into `MailService`
and read the settings at call time:

```php
$settings = $this->configurationManager->getConfiguration(
    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
    'MaiMail'
);
$message->from(new \Symfony\Component\Mime\Address(
    $settings['defaultSenderEmail'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
    $settings['defaultSenderName'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '',
));
```

The `$GLOBALS['TYPO3_CONF_VARS']['MAIL']` keys serve as a safe system-level fallback.

---

## 6 · ProcessMailQueueCommand — resolve MailService dependency

**File:** `Classes/Command/ProcessMailQueueCommand.php`

The command injects `MailService` but never calls it — all DB and send logic is duplicated
inline. This will trigger a PHPStan error (unused private property). Choose one approach:

- **Preferred:** Extract the send+log logic into `MailService::dispatch(array $row): void` and
  call it from the command. This centralises sender config (item 5) in one place.
- **Minimal:** Remove the `MailService` constructor argument from the command.

---

## 7 · Template path registration

**File:** `Configuration/TypoScript/setup.typoscript`

The backend module's Fluid templates will not be found unless the template root paths are
registered. Add:

```typoscript
module.tx_maimail {
    view {
        templateRootPaths {
            0 = EXT:mai_mail/Resources/Private/Templates/
        }
        partialRootPaths {
            0 = EXT:mai_mail/Resources/Private/Partials/
        }
        layoutRootPaths {
            0 = EXT:mai_mail/Resources/Private/Layouts/
        }
    }
}
```

Check whether `AbstractBackendController` in `mai_base` already auto-configures these paths
from the extension key before adding them — if it does, this step can be skipped.

---

## 8 · Post-install steps

After all code gaps above are closed, run this sequence in the project root:

```bash
ddev exec composer require maispace/mai-mail          # registers the extension
ddev exec vendor/bin/typo3 extension:setup mai_mail   # runs ext_tables.sql, clears caches
```

Then verify in the TYPO3 backend:
- Backend module **Web → Mai Mail** appears and renders without errors.
- `tx_maimail_queue` and `tx_maimail_log` tables exist in the database.
- Scheduler entry for `mail:process-queue` is visible under **System → Scheduler**.

---

## 9 · QA — run linters in DDEV

After the functional gaps are closed:

```bash
ddev exec bash -c "cd /var/www/html/packages/typo3-extension-mail && composer lint:check"
```

Expected issues to fix first:
- PHPStan level 5 will flag the unused `$mailService` in the command (item 6).
- `php-cs-fixer` may reformat spacing or trailing commas introduced during editing.
- `typoscript-lint` should pass as-is.

Regenerate the baseline only after fixing real errors:
```bash
composer check:phpstan:baseline
```

---

## 10 · MJML integration (future)

The `mai_mjml` extension (not yet implemented) is listed as an optional dependency for MJML
template rendering. When `mai_mjml` is ready:

1. Add `maispace/mai-mjml` as a soft dependency (`suggest` in `composer.json`).
2. Add a `MjmlRenderService` or event listener in `mai_mail` that pre-processes `.mjml`
   templates before passing HTML to `MailMessage`.
3. Guard the MJML path with `ExtensionManagementUtility::isLoaded('mai_mjml')`.

---

## 11 · Unit tests

`Tests/Unit/.gitkeep` is a placeholder. Recommended first tests:

| Class | What to test |
|---|---|
| `MailService::queue()` | Inserts correct columns; respects `scheduledAt` |
| `ProcessMailQueueCommand::execute()` | Happy path (send succeeds); retry counter increments on failure; status → `failed` after `MAX_RETRIES` |
| `MailLogRepository::findRecent()` | Respects limit and ordering |

Use TYPO3's `ConnectionPool` mock utilities from `typo3/testing-framework`.

---

## Priority order

1. **[blocker]** Items 2, 3 — without model hydration the controller will pass empty/broken data to templates.
2. **[blocker]** Items 4, 7 — template crashes prevent the backend module from rendering at all.
3. **[functional gap]** Items 1, 5, 6 — needed for resend/delete actions and actual mail dispatch.
4. **[install]** Item 8 — post-install verification sequence.
5. **[quality]** Item 9 — run QA after functional gaps are closed.
6. **[future]** Items 10, 11 — can be deferred.
