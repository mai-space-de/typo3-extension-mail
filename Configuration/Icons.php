<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-maimail-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/module-maimail.svg',
    ],
    'ext-maimail-mailqueue' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:mai_mail/Resources/Public/Icons/module-maimail.svg',
    ],
];
