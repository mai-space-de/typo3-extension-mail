<?php
declare(strict_types=1);

return [
    'ext-maispace-mai_mail' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/Extension.svg',
    ],
    'tx-maimail-queue' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/tx_maimail_queue.svg',
    ],
    'tx-maimail-log' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/tx_maimail_log.svg',
    ],
];
