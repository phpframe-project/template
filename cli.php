<?php

if (!defined('APP_MODE')) {
    define('APP_MODE', 'cli');
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "错误: Composer依赖未安装，请先运行 'composer install'\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

$app = new PHPFrame\Application();

// 运行应用
$app->run();