# maispace/mai-mail — TYPO3 Extension

Backend mail dispatch extension for TYPO3 with an async mail queue, persistent mail log, backend inspection module, queue worker command, and site-based theming hooks via TypoScript settings.

## Installation

```bash
composer require maispace/mai-mail
```

## Features

- Async mail queue with retry tracking in `tx_maimail_queue`
- Persistent mail log in `tx_maimail_log`
- Backend module for queued and logged mails
- Symfony console command `mail:process-queue`
- Site-based mail theming via TypoScript constants

## Development

```bash
composer lint:check
composer test
```
