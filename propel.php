<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

return [
    'propel' => [
        'database' => [
            'connections' => [
                'persistent' => [
                    'adapter' => 'sqlite',
                    'dsn' => 'sqlite:' . dirname(__FILE__) . '/storage/persistent.sqlite',
                    'user' => 'root',
                    'password' => '',
                    'settings' => ['charset' => 'utf8']
                ]
            ]
        ],
        'runtime' => [
            'defaultConnection' => 'persistent',
            'connections' => ['persistent']
        ],
        'generator' => [
            'defaultConnection' => 'persistent',
            'connections' => ['persistent']
        ],
        'paths' => [
            'schemaDir' => dirname(__FILE__) . '/app',
            'sqlDir' => dirname(__FILE__) . '/propel',
            'phpDir' => dirname(__FILE__) . '/propel',
            'phpConfDir' => dirname(__FILE__) . '/propel'
        ]
    ]
];