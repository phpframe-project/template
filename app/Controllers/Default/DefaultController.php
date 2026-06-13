<?php

namespace App\Controllers\Default;

use PHPFrame\Facades\Db;

class DefaultController extends Controller
{
    public function testAction()
    {
        return $this->render("/default/test.twig", ["name" => "test"]);
    }

    public function indexAction()
    {
        return $this->json([
            'code' => 0,
            'message' => '欢迎使用 PHPFrame 框架!',
            'data' => [
                'env' => app('config')->getEnvironment(),
                'debug' => config('app.debug'),
            ],
        ]);
    }

    public function dbTestAction()
    {
        try {
            $connected = Db::isConnected();
            $info = Db::getConnectionInfo();
            return $this->json([
                'code' => 0,
                'data' => [
                    'connected' => $connected,
                    'driver' => $info['driver'] ?? 'unknown',
                    'database' => $info['database'] ?? 'unknown',
                ],
            ]);
        } catch (\Exception $e) {
            return $this->error('数据库连接失败: ' . $e->getMessage(), 500);
        }
    }
}