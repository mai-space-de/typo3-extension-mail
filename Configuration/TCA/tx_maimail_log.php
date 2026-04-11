<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_mail', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maimail_log')))
    ->setDefaultConfig()
    ->setLabel('subject')
    ->setAlternativeLabelFields('recipient')
    ->setSearchFields('recipient, subject, mail_identifier')
    ->setIconFile('EXT:mai_mail/Resources/Public/Icons/tx_maimail_log.svg')
    ->setDefaultSorting('ORDER BY sent_at DESC')
    ->recordsAreOnlyAllowedInRoot()
    ->setAccessableOnlyByAdmins()
    ->recordsCanOnlyBeRead()
    ->addColumn(
        'recipient',
        $lang('tx_maimail_log.recipient'),
        ['type' => 'email', 'readOnly' => true]
    )
    ->addColumn(
        'subject',
        $lang('tx_maimail_log.subject'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'readOnly' => true]
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_log.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_maimail_log.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_log.status.failed'), 'value' => 'failed'],
                ['label' => $lang('tx_maimail_log.status.bounced'), 'value' => 'bounced'],
            ],
            'readOnly' => true,
        ]
    )
    ->addColumn(
        'sent_at',
        $lang('tx_maimail_log.sent_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'mail_identifier',
        $lang('tx_maimail_log.mail_identifier'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'readOnly' => true]
    )
    ->addTypeShowItem(
        '0',
        'recipient, subject, status, sent_at, mail_identifier'
    )
    ->getConfig();
