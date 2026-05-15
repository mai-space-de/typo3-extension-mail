<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\EmailConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_mail', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maimail_queue')))
    ->setDefaultConfig()
    ->setLabel('subject')
    ->setAlternativeLabelFields('recipient, status')
    ->setIconFile('EXT:mai_base/Resources/Public/Icons/generic_table.svg')
    ->setDefaultSorting('ORDER BY crdate DESC')
    ->recordsAreOnlyAllowedInRoot()
    ->setAccessableOnlyByAdmins()
    ->addColumn(
        'subject',
        $lang('tx_maimail_queue.subject'),
        (new InputConfig())->setSize(50)->setMax(255)
    )
    ->addColumn(
        'recipient',
        $lang('tx_maimail_queue.recipient'),
        (new EmailConfig())->setSize(50)
    )
    ->addColumn(
        'body',
        $lang('tx_maimail_queue.body'),
        (new TextConfig())->setRows(10)
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_queue.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maimail_queue.status.queued'), 'value' => 'queued'],
                ['label' => $lang('tx_maimail_queue.status.processing'), 'value' => 'processing'],
                ['label' => $lang('tx_maimail_queue.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_queue.status.failed'), 'value' => 'failed'],
            ])
    )
    ->addColumn(
        'retry_count',
        $lang('tx_maimail_queue.retry_count'),
        (new InputConfig())->setSize(10)->setReadOnly()
    )
    ->addColumn(
        'error_message',
        $lang('tx_maimail_queue.error_message'),
        (new TextConfig())->setRows(5)->setReadOnly()
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
    ->addTypeShowItem(
        '0',
        'subject, recipient, body, status, retry_count, error_message, scheduled_at, sent_at'
    )
    ->getConfig();
