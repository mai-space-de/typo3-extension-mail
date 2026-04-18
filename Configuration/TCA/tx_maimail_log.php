<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\EmailConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
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
        (new EmailConfig())->setReadOnly()
    )
    ->addColumn(
        'subject',
        $lang('tx_maimail_log.subject'),
        (new InputConfig())->setSize(50)->setMax(255)->setReadOnly()
    )
    ->addColumn(
        'status',
        $lang('tx_maimail_log.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maimail_log.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maimail_log.status.failed'), 'value' => 'failed'],
                ['label' => $lang('tx_maimail_log.status.bounced'), 'value' => 'bounced'],
            ])
            ->setReadOnly()
    )
    ->addColumn(
        'sent_at',
        $lang('tx_maimail_log.sent_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'mail_identifier',
        $lang('tx_maimail_log.mail_identifier'),
        (new InputConfig())->setSize(50)->setMax(255)->setReadOnly()
    )
    ->addTypeShowItem(
        '0',
        'recipient, subject, status, sent_at, mail_identifier'
    )
    ->getConfig();
