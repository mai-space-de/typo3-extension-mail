# Features

## Mail Queue

`mai_mail` stores pending emails in `tx_maimail_queue` and supports retry-based queue processing.

## Mail Log

Processed emails are persisted in `tx_maimail_log` for backend inspection and troubleshooting.

## Backend Module

Editors with admin access can inspect queued mail entries, review recent logs, and trigger resend/delete actions.

## Queue Worker

The `mail:process-queue` console command dispatches pending emails through TYPO3's `MailMessage` API.

## Site-based Theming

TypoScript settings expose configurable header and footer template paths for site-specific email theming.
