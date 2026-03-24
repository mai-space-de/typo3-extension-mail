<?php

declare(strict_types=1);

return [
    'tx_maimail_domain_model_mailqueue' => [
        'ctrl' => [
            'title' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue',
            'label' => 'subject',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
            'searchFields' => 'subject,sender,recipients',
            'iconfile' => 'EXT:mai_mail/Resources/Public/Icons/module-maimail.svg',
            'hideTable' => false,
            'readOnly' => true,
        ],
        'types' => [
            '1' => [
                'showitem' => 'hidden, status, priority, sender, recipients, subject, body, attachments, scheduled_at, sent_at, error_message, retry_count',
            ],
        ],
        'columns' => [
            'hidden' => [
                'exclude' => true,
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
                'config' => [
                    'type' => 'check',
                    'renderType' => 'checkboxToggle',
                    'items' => [
                        [
                            'label' => '',
                            'invertStateDisplay' => true,
                        ],
                    ],
                ],
            ],
            'sender' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.sender',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim',
                    'readOnly' => true,
                ],
            ],
            'recipients' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.recipients',
                'config' => [
                    'type' => 'text',
                    'rows' => 3,
                    'readOnly' => true,
                ],
            ],
            'subject' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.subject',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'eval' => 'trim',
                    'readOnly' => true,
                ],
            ],
            'body' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.body',
                'config' => [
                    'type' => 'text',
                    'rows' => 10,
                    'readOnly' => true,
                ],
            ],
            'attachments' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.attachments',
                'config' => [
                    'type' => 'text',
                    'rows' => 3,
                    'readOnly' => true,
                ],
            ],
            'status' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.status',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => 'Queued', 'value' => 'queued'],
                        ['label' => 'Sending', 'value' => 'sending'],
                        ['label' => 'Sent', 'value' => 'sent'],
                        ['label' => 'Failed', 'value' => 'failed'],
                        ['label' => 'Retry', 'value' => 'retry'],
                    ],
                    'readOnly' => true,
                ],
            ],
            'priority' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.priority',
                'config' => [
                    'type' => 'number',
                    'size' => 4,
                    'readOnly' => true,
                ],
            ],
            'scheduled_at' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.scheduled_at',
                'config' => [
                    'type' => 'datetime',
                    'readOnly' => true,
                ],
            ],
            'sent_at' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.sent_at',
                'config' => [
                    'type' => 'datetime',
                    'readOnly' => true,
                ],
            ],
            'error_message' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.error_message',
                'config' => [
                    'type' => 'text',
                    'rows' => 5,
                    'readOnly' => true,
                ],
            ],
            'retry_count' => [
                'exclude' => false,
                'label' => 'LLL:EXT:mai_mail/Resources/Private/Language/locallang_db.xlf:tx_maimail_domain_model_mailqueue.retry_count',
                'config' => [
                    'type' => 'number',
                    'size' => 4,
                    'readOnly' => true,
                ],
            ],
        ],
    ],
];
