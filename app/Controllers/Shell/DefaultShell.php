<?php

namespace App\Controllers\Shell;

use PHPFrame\BaseShell;

class DefaultShell extends BaseShell
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 测试命令
     * 调用: php shell.php default/test name=user1 email=user1@example.com
     */
    public function testAction($args)
    {
        $params = $this->parseArgs($args);

        // 参数验证
        if (empty($params['name']) || empty($params['email'])) {
            $this->output("错误: 缺少必要参数 name 或 email", 'error');
            return 1;
        }

        $this->output("测试参数: " . json_encode($params));

    }

}