<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\EmailConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\NumberConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
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
        (new EmailConfig())->setEval('required')->setReadOnly()
    )
    ->addColumn(
        'subject',
        $lang('tx_maimail_queue.subject'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setReadOnly()
    )
    ->addColumn(
        'body',
        $lang('tx_maimail_queue.body'),
        (new TextConfig())->setRows(20)->setCols(80)->setReadOnly()
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_queue.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maimail_queue.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_maimail_queue.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_queue.status.failed'), 'value' => 'failed'],
            ])
            ->setDefault('pending')
    )
    ->addColumn(
        'scheduled_at',
        $lang('tx_maimail_queue.scheduled_at'),
        (new DatetimeConfig())->setFormat('datetime')
    )
    ->addColumn(
        'sent_at',
        $lang('tx_maimail_queue.sent_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'retry_count',
        $lang('tx_maimail_queue.retry_count'),
        (new NumberConfig())->setFormat('integer')->setDefault(0)->setReadOnly()
    )
    ->addColumn(
        'error',
        $lang('tx_maimail_queue.error'),
        (new TextConfig())->setRows(5)->setCols(80)->setReadOnly()
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
