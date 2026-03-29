<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Mail',
    'description' => 'The **canonical mail dispatch layer** for the entire extension set. Provides an asynchronous queue, backend monitoring, site-based theming, and optional MJML rendering via `mai_mjml`. All other extensions that send email declare `maispace/mai-mail` as a dependency. `cpsit/typo3-mailqueue` must not appear in any extension\'s `require` section.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
