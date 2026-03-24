<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Register custom icons
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Imaging\IconRegistry::class
);

$iconRegistry->registerIcon(
    'ext-maimail-module',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:mai_mail/Resources/Public/Icons/module-maimail.svg']
);

$iconRegistry->registerIcon(
    'ext-maimail-mailqueue',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:mai_mail/Resources/Public/Icons/module-maimail.svg']
);
