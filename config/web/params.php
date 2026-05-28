<?php

declare(strict_types=1);

return [
    'yiisoft/session' => [
        'session' => [
            'options' => [
                'cookie_secure' => 0,
                'save_path' => dirname(__DIR__, 2) . '/runtime/sessions',
                'cookie_lifetime' => 3600,
                'gc_maxlifetime' => 3600,
            ],
        ],
    ],
];
