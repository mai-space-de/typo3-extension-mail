<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// Register scheduler task
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Maispace\MaiMail\Task\MailQueueTask::class] = [
        'extension' => 'mai_mail',
        'title' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang.xlf:task.mailqueue.title',
        'description' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang.xlf:task.mailqueue.description',
        'additionalFields' => \Maispace\MaiMail\Task\MailQueueTaskAdditionalFieldProvider::class,
    ];
}

// Register PSR-14 event listeners via Services.yaml (preferred in v12)
// The listeners are registered in Configuration/Services.yaml

// Add TypoScript setup for mail transport configuration
ExtensionManagementUtility::addTypoScriptSetup(
    '@import \'EXT:mai_mail/Configuration/TypoScript/setup.typoscript\''
);
