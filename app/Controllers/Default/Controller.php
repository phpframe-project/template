<?php

namespace App\Controllers\Default;

use PHPFrame\BaseController;

class Controller extends BaseController
{
    /**
     * 前置钩子（每个 Action 执行前调用）
     * 可在子类中覆盖以实现统一的权限检查、参数预处理等
     */
    public function before()
    {
    }

    /**
     * 后置钩子（每个 Action 执行后调用）
     * 可在子类中覆盖以实现统一的日志记录、响应后处理等
     */
    public function after()
    {
    }
}
