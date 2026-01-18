<?php
use PHPFrame\Facades\Route;
use App\Controllers\Default\DefaultController;


Route::get('/', function() {
    return '欢迎使用 PHPFrame 框架!';
});

Route::get('/test', [DefaultController::class, 'testAction']);

Route::group('/tests', function () {
    Route::get('/test1', [DefaultController::class, 'testAction']);
    Route::get('/test2', [DefaultController::class, 'testAction']);
    Route::get('/testn/{id}', [DefaultController::class, 'testAction']);
});



//// 精确匹配/admin路径
//Route::get('/admin', function() {
//    // 检查管理后台文件是否存在
//    $adminHtmlPath = ROOT_PATH . '/public/admin/index.html';
//    if (file_exists($adminHtmlPath)) {
//        // 返回管理后台的HTML页面
//        return file_get_contents($adminHtmlPath);
//    }
//    http_response_code(404);
//    return "404 Not Found";
//});
//
//// 通配符路由 - 处理所有以/admin/开头的路径，但排除静态资源
//Route::get('/admin/{path:.*}', function($path) {
//    // 检查是否为静态资源请求
//    if (str_starts_with($path, 'assets/')) {
//        // 如果是静态资源，让PHP内置服务器处理
//        return false;
//    }
//
//    // 检查管理后台文件是否存在
//    $adminHtmlPath = ROOT_PATH . '/public/admin/index.html';
//    if (file_exists($adminHtmlPath)) {
//        // 返回管理后台的HTML页面
//        return file_get_contents($adminHtmlPath);
//    }
//    http_response_code(404);
//    return "404 Not Found";
//});