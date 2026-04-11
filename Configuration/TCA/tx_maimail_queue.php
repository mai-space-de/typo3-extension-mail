<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_mail', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maimail_queue')))
    ->setDefaultConfig()
    ->setLabel('subject')
    ->setAlternativeLabelFields('recipient')
    ->setSearchFields('recipient, subject')
    ->setIconFile('EXT:mai_mail/Resources/Public/Icons/tx_maimail_queue.svg')
    ->setDefaultSorting('ORDER BY scheduled_at ASC')
    ->recordsAreOnlyAllowedInRoot()
    ->setAccessableOnlyByAdmins()
    ->addColumn(
        'recipient',
        $lang('tx_maimail_queue.recipient'),
        ['type' => 'email', 'eval' => 'required', 'readOnly' => true]
    )
    ->addColumn(
        'subject',
        $lang('tx_maimail_queue.subject'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim', 'readOnly' => true]
    )
    ->addColumn(
        'body',
        $lang('tx_maimail_queue.body'),
        ['type' => 'text', 'rows' => 20, 'cols' => 80, 'readOnly' => true]
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_queue.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_maimail_queue.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_maimail_queue.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_queue.status.failed'), 'value' => 'failed'],
            ],
            'default' => 'pending',
        ]
    )
    ->addColumn(
        'scheduled_at',
        $lang('tx_maimail_queue.scheduled_at'),
        ['type' => 'datetime', 'format' => 'datetime']
    )
    ->addColumn(
        'sent_at',
        $lang('tx_maimail_queue.sent_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'retry_count',
        $lang('tx_maimail_queue.retry_count'),
        ['type' => 'number', 'format' => 'integer', 'default' => 0, 'readOnly' => true]
    )
    ->addColumn(
        'error',
        $lang('tx_maimail_queue.error'),
        ['type' => 'text', 'rows' => 5, 'cols' => 80, 'readOnly' => true]
    )
    ->addPalette(
        'dates',
        $lang('palette.dates'),
        'scheduled_at, sent_at'
    )
    ->addTypeShowItem(
        '0',
        'recipient, subject, body, status, --palette--;;dates, retry_count, error'
    )
    ->getConfig();
