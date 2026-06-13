<?php

namespace App\Middleware;

use PHPFrame\Middleware\MiddlewareInterface;

/**
 * CORS 中间件示例
 * 处理跨域请求
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        // FPM 模式设置 CORS 头
        if (function_exists('header')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');

            // OPTIONS 预检请求直接返回
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(204);
                return '';
            }
        }

        return $next($request);
    }
}
