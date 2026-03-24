<?php

declare(strict_types=1);

use Maispace\MaiMail\Controller\ComposeController;
use Maispace\MaiMail\Controller\InboxController;
use Maispace\MaiMail\Controller\MailQueueController;
use Maispace\MaiMail\Controller\StatsController;

return [
    'maimail' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaceSupport' => false,
        'iconIdentifier' => 'ext-maimail-module',
        'path' => '/module/maimail',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            MailQueueController::class => ['index', 'show', 'delete', 'retry', 'sendNow'],
        ],
    ],
    'maimail_queue' => [
        'parent' => 'maimail',
        'access' => 'user',
        'iconIdentifier' => 'ext-maimail-mailqueue',
        'path' => '/module/maimail/queue',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_mod_queue.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            MailQueueController::class => ['index', 'show', 'delete', 'retry', 'sendNow'],
        ],
    ],
    'maimail_stats' => [
        'parent' => 'maimail',
        'access' => 'user',
        'iconIdentifier' => 'ext-maimail-module',
        'path' => '/module/maimail/stats',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_mod_stats.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            StatsController::class => ['index'],
        ],
    ],
    'maimail_compose' => [
        'parent' => 'maimail',
        'access' => 'user',
        'iconIdentifier' => 'ext-maimail-module',
        'path' => '/module/maimail/compose',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_mod_compose.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            ComposeController::class => ['index', 'send'],
        ],
    ],
    'maimail_inbox' => [
        'parent' => 'maimail',
        'access' => 'user',
        'iconIdentifier' => 'ext-maimail-module',
        'path' => '/module/maimail/inbox',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_mod_inbox.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            InboxController::class => ['index', 'read', 'delete'],
        ],
    ],
];
