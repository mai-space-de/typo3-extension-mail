<?php
declare(strict_types=1);

use Maispace\MaiMail\Controller\Backend\MailBackendController;

return [
    'mai_mail' => [
        'parent' => 'web',
        'access' => 'admin',
        'workspaces' => 'online',
        'path' => '/module/mai-mail',
        'iconIdentifier' => 'mai-backend-module',
        'labels' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'MaiMail',
        'controllerActions' => [
            MailBackendController::class => ['index', 'resend', 'delete'],
        ],
    ],
];
