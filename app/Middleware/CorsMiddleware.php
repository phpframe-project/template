<?php

namespace App\Middleware;

use PHPFrame\Middleware\MiddlewareInterface;
use PHPFrame\Runtime;

/**
 * CORS 中间件示例
 * 处理跨域请求
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        if (Runtime::isFpm()) {
            // FPM 模式：直接设置响应头
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');

            // OPTIONS 预检请求直接返回
            if (app('request')->getMethod() === 'OPTIONS') {
                http_response_code(204);
                return '';
            }
        } elseif (Runtime::isCli()) {
            // CLI 模式（ReactPHP）：OPTIONS 预检请求返回空响应
            if (app('request')->getMethod() === 'OPTIONS') {
                return new \React\Http\Message\Response(
                    204,
                    [
                        'Access-Control-Allow-Origin' => '*',
                        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                    ],
                    ''
                );
            }
            // 非 OPTIONS 请求的 CORS 头由 Response 自动处理
        }

        return $next($request);
    }
}
