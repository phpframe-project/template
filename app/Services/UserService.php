<?php

namespace App\Services;

use App\Models\User;
use PHPFrame\Facades\Db;
use PHPFrame\Facades\Cache;
use PHPFrame\Facades\Log;

/**
 * 用户服务示例
 * 演示 Service 层封装业务逻辑，通过容器注册后使用
 */
class UserService
{
    /**
     * 获取用户列表（带缓存）
     */
    public function listUsers(int $page = 1, int $perPage = 15): array
    {
        return Cache::remember("users:page:{$page}", 60, function () use ($page, $perPage) {
            return User::active()
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page)
                ->toArray();
        });
    }

    /**
     * 根据 ID 获取用户
     */
    public function getUser(int $id): ?User
    {
        return Cache::remember("users:{$id}", 300, function () use ($id) {
            return User::find($id);
        });
    }

    /**
     * 创建用户
     */
    public function createUser(array $data): User
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return Db::transaction(function () use ($data) {
            $user = User::create($data);
            Log::info('User created', ['user_id' => $user->id]);
            Cache::delete("users:page:1");
            return $user;
        });
    }

    /**
     * 更新用户
     */
    public function updateUser(int $id, array $data): ?User
    {
        return Db::transaction(function () use ($id, $data) {
            $user = User::find($id);
            if (!$user) {
                return null;
            }

            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $user->fill($data)->save();
            Cache::delete("users:{$id}");
            Log::info('User updated', ['user_id' => $id]);
            return $user;
        });
    }

    /**
     * 删除用户
     */
    public function deleteUser(int $id): bool
    {
        return Db::transaction(function () use ($id) {
            $user = User::find($id);
            if (!$user) {
                return false;
            }

            $user->delete();
            Cache::delete("users:{$id}");
            Cache::deleteByPattern('users:page:*');
            Log::info('User deleted', ['user_id' => $id]);
            return true;
        });
    }
}
