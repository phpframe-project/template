<?php

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => runtime_path('cache'),
            'lock_path' => runtime_path('cache'),
        ],
        
        'redis' => [
            'driver' => 'redis',
            'client' => env('REDIS_CLIENT', 'predis'),
            'cluster' => env('REDIS_CLUSTER', 'predis'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', ''),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'phpframe'),
];