<?php
declare(strict_types=1);

return [
    'ext-maispace-mai_mail' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/Extension.svg',
    ],
    'tx-maimail-queue' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_base/Resources/Public/Icons/generic_table.svg',
    ],
    'tx-maimail-log' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_base/Resources/Public/Icons/generic_table.svg',
    ],
];
