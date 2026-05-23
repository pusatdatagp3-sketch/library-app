<?php

declare(strict_types=1);

return [
    'yiisoft/session' => [
        'session' => [
            'options' => [
                'cookie_secure' => 0,
                'save_path' => dirname(__DIR__, 2) . '/runtime/sessions',
            ],
        ],
    ],
];
