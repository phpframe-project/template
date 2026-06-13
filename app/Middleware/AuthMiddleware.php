<?php

namespace App\Middleware;

use PHPFrame\Middleware\MiddlewareInterface;

/**
 * 认证中间件示例
 * 验证 Bearer Token
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        $token = app('request')->getBearerToken();

        if (!$token || !$this->validateToken($token)) {
            return ['code' => 401, 'message' => 'Unauthorized'];
        }

        return $next($request);
    }

    private function validateToken(string $token): bool
    {
        // 替换为实际的 Token 验证逻辑
        $secret = config('app.secret', '');
        return !empty($secret) && $token === $secret;
    }
}
