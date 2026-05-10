<?php
declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
        '@import "EXT:mai_mail/Configuration/TypoScript/constants.typoscript"'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        '@import "EXT:mai_mail/Configuration/TypoScript/setup.typoscript"'
    );
})();
