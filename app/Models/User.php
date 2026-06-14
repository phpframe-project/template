<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户模型示例
 * 演示 Eloquent Model 在 PHPFrame 中的基本用法
 */
class User extends Model
{
    protected $table = 'users';

    // 允许批量赋值的字段
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    // 隐藏字段（序列化时排除）
    protected $hidden = [
        'password',
    ];

    // 字段类型转换
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * 查询活跃用户
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
