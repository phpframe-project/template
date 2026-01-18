<?php

return [
    'name' => env('APP_NAME', 'PHPFrame'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', ''),
    'key' => env('APP_KEY', ''),
    'secret' => env('APP_SECRET', ''),
    'timezone' => env('APP_TIMEZONE', 'Asia/Shanghai'),
    'admin_token_expiry' => 7*24*3600,
    'server_ip' => env('APP_SERVER_IP', '127.0.0.1'),

    'config_map' => [
//        'storage' => CONFIG_PATH . '/storage.php',
//        'notify' => CONFIG_PATH . '/notify.php',
    ],
    
    // 服务别名
    'aliases' => [
        'App' => PHPFrame\Facades\App::class,
        'Config' => PHPFrame\Facades\Config::class,
        'Db' => PHPFrame\Facades\Db::class,
        'Cache' => PHPFrame\Facades\Cache::class,
        'Log' => PHPFrame\Facades\Log::class,
        'Route' => PHPFrame\Facades\Route::class,
        'Request' => PHPFrame\Facades\Request::class,
    ],
    
    // 服务配置
    'services' => [
        // 特殊服务的配置（需要依赖注入的复杂服务）
        PHPFrame\Database\Migration::class => [
            'class' => 'PHPFrame\Database\Migration',
            'dependencies' => ['db']
        ],
        
        App\Controllers\Shell\DatabaseShell::class => [
            'class' => 'App\Controllers\Shell\DatabaseShell',
            'dependencies' => [PHPFrame\Database\Migration::class]
        ]
    ]
];