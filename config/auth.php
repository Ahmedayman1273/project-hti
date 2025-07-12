<?php

return [

    'defaults' => [
        'guard' => 'web',   // ممكن تخليه 'api' لو تحب، لكن 'web' عادي للمصادقة مع الجلسات
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [                // مهم جداً تضيفه
            'driver' => 'sanctum',
            'provider' => 'users',
            // 'hash' => false,     // ممكن تضيف هذا إذا تحب
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
