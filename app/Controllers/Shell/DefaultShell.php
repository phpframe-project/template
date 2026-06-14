<?php

namespace App\Controllers\Shell;

use PHPFrame\BaseShell;

class DefaultShell extends BaseShell
{
    /**
     * 测试命令
     * 调用: php shell.php default/test name=user1 email=user1@example.com
     */
    public function testAction()
    {
        $params = $this->getParams();

        // 参数验证（注意：empty('0') 返回 true，需用 === 显式判断）
        if (!isset($params['name']) || $params['name'] === '' || !isset($params['email']) || $params['email'] === '') {
            $this->output("错误: 缺少必要参数 name 或 email", 'error');
            return 1;
        }

        $this->output("测试参数: " . json_encode($params));
        $this->log("测试命令执行完成，参数: " . json_encode($params));

        return 0;
    }
}