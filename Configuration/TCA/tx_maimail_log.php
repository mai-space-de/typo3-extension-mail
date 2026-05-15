<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_mail', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maimail_log')))
    ->setDefaultConfig()
    ->setLabel('subject')
    ->setAlternativeLabelFields('recipient, status')
    ->setIconFile('EXT:mai_base/Resources/Public/Icons/generic_table.svg')
    ->setDefaultSorting('ORDER BY crdate DESC')
    ->recordsAreOnlyAllowedInRoot()
    ->setAccessableOnlyByAdmins()
    ->addColumn(
        'subject',
        $lang('tx_maimail_log.subject'),
        (new InputConfig())->setSize(50)->setMax(255)->setReadOnly()
    )
    ->addColumn(
        'recipient',
        $lang('tx_maimail_log.recipient'),
        (new InputConfig())->setSize(50)->setMax(255)->setReadOnly()
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_log.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maimail_log.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_log.status.failed'), 'value' => 'failed'],
            ])
    )
    ->addColumn(
        'sent_at',
        $lang('tx_maimail_log.sent_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'error_message',
        $lang('tx_maimail_log.error_message'),
        (new TextConfig())->setRows(5)->setReadOnly()
    )
    ->addTypeShowItem(
        '0',
        'subject, recipient, status, sent_at, error_message'
    )
    ->getConfig();
