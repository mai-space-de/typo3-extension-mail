<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Mail',
    'description' => 'Mail queue and logging extension for TYPO3 backend mail dispatch with site-based theming.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3/cms-core' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
