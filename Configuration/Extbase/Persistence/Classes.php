<?php

declare(strict_types=1);

return [
    \Maispace\MaiMail\Domain\Model\MailQueue::class => [
        'tableName' => 'tx_maimail_queue',
    ],
    \Maispace\MaiMail\Domain\Model\MailLog::class => [
        'tableName' => 'tx_maimail_log',
    ],
];
