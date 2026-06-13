<?php
use PHPFrame\Facades\Route;
use App\Controllers\Default\DefaultController;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;

// 全局中间件（所有路由生效）
Route::middleware(new CorsMiddleware());

// 路由级中间件别名注册
Route::registerMiddleware('auth', new AuthMiddleware());

// 首页
Route::get('/', [DefaultController::class, 'indexAction']);

// 模板渲染示例
Route::get('/test', [DefaultController::class, 'testAction']);

// 数据库连接测试
Route::get('/db-test', [DefaultController::class, 'dbTestAction']);

// 路由组
Route::group('/tests', function () {
    Route::get('/test1', [DefaultController::class, 'testAction']);
    Route::get('/test2', [DefaultController::class, 'testAction']);
    Route::get('/testn/{id}', [DefaultController::class, 'testAction']);
});

// 为指定 handler 绑定路由级中间件
// Route::handlerMiddleware('App\Controllers\Default\DefaultController@dbTestAction', ['auth']);
