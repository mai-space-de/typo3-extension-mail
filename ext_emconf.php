<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Mail',
    'description' => 'TYPO3 mail extension with queue management, backend module, MJML rendering support and site-based theming',
    'category' => 'be',
    'author' => 'Mai Space',
    'author_email' => 'info@mai.space',
    'author_company' => 'Mai Space',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
