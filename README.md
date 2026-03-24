# Mai Mail – TYPO3 Mail Extension

[![CI](https://github.com/maispace/mai-mail/actions/workflows/ci.yml/badge.svg)](https://github.com/maispace/mai-mail/actions/workflows/ci.yml)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

A robust TYPO3 v12 extension for mail queue management, backend monitoring,
and IMAP/POP3 inbox viewing.

---

## Features

- **Mail Queue** – Reliably queue, schedule, and retry email delivery
- **Backend Module** – Visual queue management (list, show, delete, retry, send now)
- **Statistics Dashboard** – Aggregate counts and success rate
- **Compose UI** – Send or queue emails from the TYPO3 backend with HTML support
- **IMAP/POP3 Inbox Viewer** – Browse incoming mail from the backend
- **PSR-14 Events** – `MailQueuedEvent`, `MailSentEvent`, `MailFailedEvent`
- **Scheduler Task** – Process the queue automatically
- **Priority & Scheduling** – Per-message priority and future delivery times
- **Extended `MailMessage`** – Drop-in enhancement of TYPO3's core mail class

---

## Requirements

| Requirement | Version |
|---|---|
| TYPO3 CMS | ^12.4 |
| PHP | ^8.2 |
| PHP imap ext | optional (inbox only) |

---

## Installation

```bash
composer require maispace/mai-mail
```

Then activate in the Extension Manager and run database compare.

---

## Quick Start

### Queue a mail via service

```php
use Maispace\MaiMail\Service\MailQueueService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$service = GeneralUtility::makeInstance(MailQueueService::class);
$service->add(
    sender: 'noreply@example.com',
    recipients: ['user@example.com' => 'John Doe'],
    subject: 'Hello',
    body: '<p>Hello World</p>'
);
```

### Use the extended MailMessage

```php
use Maispace\MaiMail\Mail\MailMessage;

$message = GeneralUtility::makeInstance(MailMessage::class);
$message->from('noreply@example.com')
        ->to('user@example.com')
        ->subject('Hello')
        ->htmlWithTextFallback('<p>Hello World</p>')
        ->queue(priority: 5);
```

---

## Backend Module

| Sub-module | Path | Description |
|---|---|---|
| Queue | `/module/maimail/queue` | Manage queued emails |
| Stats | `/module/maimail/stats` | Statistics dashboard |
| Compose | `/module/maimail/compose` | Compose & send emails |
| Inbox | `/module/maimail/inbox` | IMAP/POP3 inbox viewer |

---

## PSR-14 Events

| Event | Fired when |
|---|---|
| `MailQueuedEvent` | Mail added to queue |
| `MailSentEvent` | Mail sent successfully |
| `MailFailedEvent` | Sending failed |

---

## Development

```bash
composer install
vendor/bin/phpunit --configuration phpunit.xml
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php
```

---

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.