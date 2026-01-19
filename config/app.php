<?php

return [
    'name' => env('APP_NAME', 'PHPFrame'),
    'env' => env('APP_ENV', 'prod'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', ''),
    'key' => env('APP_KEY', ''),
    'secret' => env('APP_SECRET', ''),
    'timezone' => env('APP_TIMEZONE', 'Asia/Shanghai'),
    'admin_token_expiry' => 7*24*3600,
    'server_ip' => env('APP_SERVER_IP', '127.0.0.1'),

    'config_map' => [
//        'storage' => require_once CONFIG_PATH . '/storage.php',
//        'notify' => require_once CONFIG_PATH . '/notify.php',
    ],
    
];