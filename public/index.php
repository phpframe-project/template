<?php

// 定义运行模式为FPM
if (!defined('APP_MODE')) {
    define('APP_MODE', 'fpm');
}

// 检查Composer是否已安装
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    echo "错误: Composer依赖未安装，请先运行 'composer install'\n";
    exit(1);
}

// 加载Composer自动加载
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 创建应用实例
$app = require PHPFRAME_PATH . '/app.php';

// 运行应用
$app->run();