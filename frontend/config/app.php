<?php

return [
    'name' => env('APP_NAME', '24hStore Warranty Lookup'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'force_https' => env('FORCE_HTTPS', false),
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    'locale' => env('APP_LOCALE', 'vi'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'vi_VN'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'file'),
    ],
];
