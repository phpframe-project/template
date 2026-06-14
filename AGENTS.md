# AGENTS.md

本文件为 AI 编码助手提供项目上下文，帮助生成符合项目规范的代码。

## 项目概述

PHPFrame 是一个支持 FPM / CLI（ReactPHP 常驻内存）/ Shell 三模式的 PHP 框架。本项目是基于 PHPFrame 的应用模板。

## 技术栈

- PHP 8.1+（使用 Fiber、命名参数、联合类型等特性）
- PHPFrame 框架（核心依赖）
- illuminate/database（Eloquent 查询构建器）
- illuminate/cache（缓存抽象）
- Monolog（日志）
- Twig（模板引擎）
- ReactPHP（CLI 常驻服务器）
- nikic/fast-route（路由引擎）

## 项目结构

```
app/
  Controllers/
    Default/          # HTTP 控制器，继承 BaseController → Controller
    Shell/            # Shell 控制器，继承 BaseShell
  Library/            # 自定义类库
  Middleware/         # 中间件（实现 MiddlewareInterface）
  Models/             # 数据模型（Eloquent ORM）
  Services/           # 业务服务
config/               # 配置文件，文件名即配置组键名
database/
  field_template.php  # 模型字段生成模板
resources/
  templates/          # Twig 模板
routes/
  default.php         # HTTP 路由（GET/POST/PUT/DELETE）
  shell.php           # Shell 路由
public/index.php      # FPM 入口
cli.php               # CLI 入口（ReactPHP）
shell.php             # Shell 入口
```

## 编码规范

### 命名

- 控制器：`PascalCase` + `Controller` 后缀，如 `UserController`
- 控制器方法：`camelCase` + `Action` 后缀，如 `listAction`、`createAction`
- Shell 控制器：`PascalCase` + `Shell` 后缀，如 `DatabaseShell`
- Model：`PascalCase` 单数形式，如 `User`、`OrderItem`
- Service：`PascalCase` + `Service` 后缀，如 `UserService`
- 路由路径：`snake_case`，如 `/user-profile`
- 配置键：`snake_case`，如 `database.connections.default.host`

### 控制器

HTTP 控制器继承链：`BaseController` → `Controller`（项目基类）→ 具体控制器

```php
namespace App\Controllers\Default;

use PHPFrame\Facades\Db;

class UserController extends Controller
{
    public function listAction()
    {
        $page = $this->getParam('page', 1);
        return $this->json(['code' => 0, 'data' => []]);
    }
}
```

Shell 控制器继承 `BaseShell`：

```php
namespace App\Controllers\Shell;

use PHPFrame\BaseShell;

class TaskShell extends BaseShell
{
    public function processAction()
    {
        $this->output("开始处理...");
        $this->log("任务执行完成");
    }
}
```

### 获取请求参数

```php
// 控制器中
$this->getParam('key');           // 获取单个参数
$this->getParam('key', 'default'); // 带默认值
$this->getParams();               // 获取所有参数

// Request 对象
$this->request->query('key');     // GET 参数
$this->request->post('key');      // POST 参数
$this->request->query();          // 所有 GET 参数
$this->request->post();           // 所有 POST 参数
```

**禁止**直接访问 `$_GET`、`$_POST`、`$_REQUEST`、`$_SERVER` 等超全局变量。

### JSON 请求体

框架自动解析 `Content-Type: application/json` 请求，JSON 数据合并到 `post()` 可访问的属性中，无需手动处理：

```php
// 客户端发送 JSON: {"name": "John", "email": "john@example.com"}
$name = $this->request->post('name');   // 'John'
$email = $this->getParam('email');      // 'john@example.com'
```

> FPM 模式下 `createFromGlobals()` 会自动将 JSON 数据合并到 `post()`；CLI 模式下 ReactPHP 已自动解析到 `getParsedBody()`，同样通过 `post()` 访问。框架不会修改 `$_POST` / `$_REQUEST` 超全局变量。

### 响应

```php
// JSON 响应
return $this->json(['code' => 0, 'data' => $users]);

// 模板渲染
return $this->render('/default/test.twig', ['name' => 'PHPFrame']);

// 重定向（不会强制 exit，中间件后置逻辑仍会执行）
return $this->redirect('/login', 302);
```

### 门面

使用门面进行静态调用：

```php
use PHPFrame\Facades\Log;
use PHPFrame\Facades\Db;
use PHPFrame\Facades\Cache;
use PHPFrame\Facades\Config;
use PHPFrame\Facades\App;
use PHPFrame\Facades\Route;

Log::info('message', ['context' => 'data']);
Db::table('users')->get();
Cache::set('key', 'value', 3600);
Config::get('app.name');
App::get('db');         // 从容器获取服务
App::has('cache');      // 检查服务是否存在
App::set('my', fn() => new MyService());  // 注册服务
```

### 中间件

```php
use PHPFrame\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        // 前置逻辑
        $response = $next($request);
        // 后置逻辑
        return $response;
    }
}
```

注册：

```php
// 全局
Route::middleware(new CorsMiddleware());

// 路由级
Route::registerMiddleware('auth', new AuthMiddleware());
Route::handlerMiddleware('App\Controllers\UserController@profile', ['auth']);
```

### 依赖注入

```php
// 单例（默认）
app()->set('my_service', function ($c) {
    return new MyService($c->get('db'));
});

// 原型（每次 get() 返回新实例）
app()->prototype('my_request', function ($c) {
    return new MyRequest();
});

// 使用
$service = app('my_service');
$service = app()->get('my_service');
```

> 注意：`request` 服务在 CLI 常驻内存模式下自动注册为原型服务，每次解析返回新实例，避免请求间状态污染。

### 配置

配置文件放在 `config/` 目录，返回数组：

```php
// config/my_config.php
return [
    'key' => env('MY_CONFIG_KEY', 'default'),
];
```

环境覆盖：`config/my_config.production.php` 会在 `APP_ENV=production` 时深度合并。

访问：`config('my_config.key')` 或 `Config::get('my_config.key')`。

### 日志

统一使用框架 Logger，**禁止**直接 `file_put_contents` 写日志文件：

```php
use PHPFrame\Facades\Log;

Log::info('message');
Log::error('error', ['context' => $data]);
Log::warning('warning');
Log::debug('debug');
```

Shell 控制器中使用 `$this->log()` 方法，会自动通过框架 Logger 记录。

### 缓存

```php
use PHPFrame\Facades\Cache;

Cache::set('key', 'value', 3600);
$value = Cache::get('key');
Cache::delete('key');
Cache::deleteByPattern('users:*');  // 仅 Redis
Cache::remember('key', 3600, fn() => expensiveQuery());
```

### 数据库

```php
use PHPFrame\Facades\Db;

// 查询构建器
Db::table('users')->where('active', 1)->get();

// 事务
Db::transaction(function () {
    Db::table('users')->insert([...]);
});

// 原生 SQL
Db::select('SELECT * FROM users WHERE id = ?', [$id]);
```

### Model

Model 使用 Eloquent ORM，放在 `app/Models/` 目录：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'active'];
    protected $hidden = ['password'];
    protected $casts = ['active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
```

使用：

```php
use App\Models\User;

// 查询
$users = User::active()->get();
$user = User::find($id);

// 创建
$user = User::create(['name' => 'John', 'email' => 'john@example.com']);

// 更新
$user->update(['name' => 'Jane']);

// 删除
$user->delete();
```

> 可通过 `php shell.php database/build-model-fields` 自动生成字段常量类到 `app/Models/Fields/`。

### Service

Service 封装业务逻辑，放在 `app/Services/` 目录，通过容器注册使用：

```php
namespace App\Services;

use App\Models\User;
use PHPFrame\Facades\Db;
use PHPFrame\Facades\Cache;
use PHPFrame\Facades\Log;

class UserService
{
    public function listUsers(int $page = 1): array
    {
        return Cache::remember("users:page:{$page}", 60, function () use ($page) {
            return User::active()->paginate(15, ['*'], 'page', $page)->toArray();
        });
    }

    public function createUser(array $data): User
    {
        return Db::transaction(function () use ($data) {
            $user = User::create($data);
            Log::info('User created', ['user_id' => $user->id]);
            return $user;
        });
    }
}
```

注册到容器（在 `routes/default.php` 或自定义引导文件中）：

```php
app()->set(UserService::class, function ($c) {
    return new UserService();
});
```

在控制器中使用：

```php
$userService = app(UserService::class);
$users = $userService->listUsers($page);
```

> 推荐将业务逻辑放在 Service 层，控制器只负责参数获取、调用 Service、返回响应。

## 常见模式

### 新增 HTTP 控制器

1. 在 `app/Controllers/Default/` 创建控制器类，继承 `Controller`
2. 方法名以 `Action` 结尾
3. 在 `routes/default.php` 注册路由

### 新增 Shell 命令

1. 在 `app/Controllers/Shell/` 创建 Shell 类，继承 `BaseShell`
2. 方法名以 `Action` 结尾
3. 在 `routes/shell.php` 注册路由

### 新增配置

1. 在 `config/` 目录创建 `my_config.php`，返回数组
2. 通过 `config('my_config.key')` 访问

### 新增中间件

1. 实现 `PHPFrame\Middleware\MiddlewareInterface`
2. 在路由文件中注册（全局或路由级）

### 新增 Model

1. 在 `app/Models/` 创建 Model 类，继承 `Illuminate\Database\Eloquent\Model`
2. 定义 `$table`、`$fillable`、`$hidden`、`$casts` 等属性
3. 可选：运行 `php shell.php database/build-model-fields` 生成字段常量类

### 新增 Service

1. 在 `app/Services/` 创建 Service 类
2. 通过 `app()->set(ServiceClass::class, ...)` 注册到容器
3. 在控制器中通过 `app(ServiceClass::class)` 获取使用

## CLI 常驻模式注意事项

- 控制器不要用类属性存储请求级状态，使用 `$this->request`
- 自定义服务有请求级状态时，注册到 `RequestIsolationManager`
- 日志文件按日期自动轮转，无需手动处理
- Worker 进程崩溃后主进程会自动重启
- `request` 服务在 CLI 模式下为原型服务，每次解析返回新实例

## 参数验证注意事项

- **禁止**使用 `empty()` 验证必填字段，`empty('0')` 返回 `true` 会导致 "0" 值被误判为空
- 使用 `!isset($value) || $value === ''` 替代 `empty($value)` 进行必填验证
- 框架 `Validation` 组件已修复此问题，自定义验证逻辑也需注意

## 控制器 before() 钩子

- `before()` 中抛出的异常会正常传播到异常处理器，不会被静默吞掉
- 可用于权限检查、参数预处理等前置逻辑

## 辅助函数

| 函数 | 说明 |
|------|------|
| `app($id)` | 获取容器实例或解析服务 |
| `config($key, $default)` | 读取配置（仅支持获取，设置用 `app('config')->set()`） |
| `env($key, $default)` | 读取环境变量 |
| `root_path($path)` | 项目根目录 |
| `config_path($path)` | config 目录 |
| `runtime_path($path)` | runtime 目录 |
| `public_path($path)` | public 目录 |
| `resource_path($path)` | resources 目录 |
| `database_path($path)` | database 目录 |
| `logger()` | 获取 Logger 实例 |
| `dd(...$vars)` | 调试输出并终止 |
| `array_get($array, $key, $default)` | 安全获取数组值（支持点号分隔键） |
| `isolate_request($force)` | 执行请求级状态隔离（常驻内存模式） |
