# PHPFrame 项目模板

基于 [PHPFrame](https://github.com/phpframe-project/phpframe) 框架的项目模板，支持 FPM / CLI（ReactPHP 常驻内存）/ Shell 三模式运行。

## 快速开始

```bash
# 创建项目
composer create-project phpframe-project/template my-project
cd my-project

# 环境配置
cp .env.example .env
# 编辑 .env 配置数据库、Redis 等

# 启动开发服务器
php -S localhost:8000 -t public/

# 启动 CLI 常驻服务器（支持多 Worker）
php cli.php server start --host=0.0.0.0 --port=8000 --worker=4

# 执行 Shell 命令
php shell.php default/test name=user1 email=user1@example.com
```

## 目录结构

```
my-project/
├── app/
│   ├── Controllers/
│   │   ├── Default/              # HTTP 控制器（继承 BaseController）
│   │   │   ├── Controller.php    # 控制器基类（可添加 before/after 钩子）
│   │   │   └── DefaultController.php
│   │   └── Shell/                # Shell 控制器（继承 BaseShell）
│   │       ├── DefaultShell.php
│   │       └── DatabaseShell.php
│   ├── Library/                  # 自定义类库
│   ├── Models/                   # 数据模型
│   └── Services/                 # 业务服务
├── config/                       # 配置文件（自动加载）
│   ├── app.php                   # 应用配置
│   ├── database.php              # 数据库配置
│   ├── cache.php                 # 缓存配置
│   ├── log.php                   # 日志配置
│   └── exception.php             # 异常处理配置
├── database/
│   └── field_template.php        # 模型字段生成模板
├── public/
│   └── index.php                 # FPM 入口
├── resources/
│   └── templates/                # Twig 模板
├── routes/
│   ├── default.php               # HTTP 路由
│   └── shell.php                 # Shell 路由
├── runtime/                      # 运行时目录（日志、缓存，自动创建）
├── cli.php                       # CLI 入口
├── shell.php                     # Shell 入口
├── .env.example                  # 环境变量示例
└── composer.json
```

## 三种运行模式

### FPM 模式

传统 PHP-FPM 或内置开发服务器，适合传统 Web 部署：

```bash
php -S localhost:8000 -t public/
```

### CLI 模式（ReactPHP 常驻内存）

高性能常驻内存服务器，支持多 Worker 进程：

```bash
# 前台运行
php cli.php server start --host=0.0.0.0 --port=8000 --worker=4

# 守护进程
php cli.php server start --host=0.0.0.0 --port=8000 --worker=4 --daemon

# 停止
php cli.php server stop
```

### Shell 模式

命令行任务执行，适合定时任务、数据迁移、批量处理：

```bash
php shell.php default/test name=user1 email=user1@example.com
php shell.php database/tables
php shell.php database/describe table_name
php shell.php database/build-structure
php shell.php database/build-model-fields
```

## 核心用法

### 路由

在 `routes/default.php` 中定义 HTTP 路由：

```php
use PHPFrame\Facades\Route;
use App\Controllers\Default\DefaultController;

// 闭包
Route::get('/', function() {
    return 'Hello World';
});

// 控制器方法
Route::get('/users', [UserController::class, 'listAction']);
Route::post('/users', [UserController::class, 'createAction']);
Route::put('/users/{id}', [UserController::class, 'updateAction']);
Route::delete('/users/{id}', [UserController::class, 'deleteAction']);

// 路由组
Route::group('/api', function () {
    Route::get('/users', [UserController::class, 'listAction']);
});
```

在 `routes/shell.php` 中定义 Shell 路由：

```php
Route::shell('default/test', [DefaultShell::class, 'testAction']);
```

### 控制器

HTTP 控制器继承 `BaseController`：

```php
namespace App\Controllers\Default;

use PHPFrame\BaseController;

class UserController extends Controller
{
    public function listAction()
    {
        $page = $this->getParam('page', 1);
        $users = Db::table('users')->paginate(15, ['*'], 'page', $page);
        return $this->json(['code' => 0, 'data' => $users]);
    }

    public function createAction()
    {
        $data = $this->request->post();
        // 验证并创建...
        return $this->json(['code' => 0, 'message' => 'created']);
    }
}
```

Shell 控制器继承 `BaseShell`：

```php
namespace App\Controllers\Shell;

use PHPFrame\BaseShell;

class DefaultShell extends BaseShell
{
    public function testAction()
    {
        $params = $this->getParams();
        $this->output("参数: " . json_encode($params));
        $this->log("执行测试命令");
    }
}
```

### 中间件

```php
use PHPFrame\Facades\Route;
use App\Middleware\AuthMiddleware;

// 全局中间件
Route::middleware(new CorsMiddleware());

// 路由级中间件
Route::registerMiddleware('auth', new AuthMiddleware());
Route::handlerMiddleware('App\Controllers\UserController@profile', ['auth']);
```

### 门面

```php
use PHPFrame\Facades\Log;
use PHPFrame\Facades\Db;
use PHPFrame\Facades\Cache;
use PHPFrame\Facades\Config;

Log::info('User logged in', ['user_id' => 123]);
$users = Db::table('users')->get();
Cache::set('key', 'value', 3600);
$name = Config::get('app.name');
```

### 依赖注入

```php
// 单例服务
app()->set('my_service', function ($c) {
    return new MyService($c->get('db'));
});

// 原型服务（每次解析返回新实例）
app()->prototype('request', function ($c) {
    return Request::createFromGlobals();
});

// 使用
$service = app('my_service');
```

### 配置

配置文件放在 `config/` 目录下，自动加载。支持环境覆盖：

```
config/app.php                # 基础配置
config/app.production.php     # 生产环境覆盖（APP_ENV=production 时自动合并）
```

```php
// 读取
config('app.name');
config('database.connections.default.host', '127.0.0.1');

// 运行时修改
config('app.debug', true);
```

### 缓存

```php
use PHPFrame\Facades\Cache;

Cache::set('key', 'value', 3600);
$value = Cache::get('key');
Cache::delete('key');
Cache::deleteByPattern('users:*');  // 模式删除（Redis）
```

### 数据库

```php
use PHPFrame\Facades\Db;

// 查询构建器
$users = Db::table('users')->where('active', 1)->get();

// 事务
Db::transaction(function () {
    Db::table('users')->insert([...]);
    Db::table('orders')->insert([...]);
});
```

### 日志

```php
use PHPFrame\Facades\Log;

Log::info('Info message');
Log::error('Error message', ['context' => $data]);
Log::warning('Warning message');
```

### 模板渲染

```php
// 在控制器中
return $this->render('/default/test.twig', ['name' => 'PHPFrame']);
```

### JSON 响应

```php
return $this->json(['code' => 0, 'data' => $users]);
```

### 重定向

```php
return $this->redirect('/login', 302);
```

## 环境变量

复制 `.env.example` 为 `.env` 并按需修改：

| 变量 | 说明 | 默认值 |
|------|------|--------|
| `APP_NAME` | 应用名称 | PHPFrame |
| `APP_ENV` | 环境（local/production） | local |
| `APP_DEBUG` | 调试模式 | true |
| `DB_HOST` | 数据库主机 | 127.0.0.1 |
| `DB_DATABASE` | 数据库名 | phpframe |
| `CACHE_DRIVER` | 缓存驱动（redis/file） | redis |
| `REDIS_HOST` | Redis 主机 | 127.0.0.1 |

## 环境要求

- PHP >= 8.1
- ext-pdo
- ext-pcntl（多 Worker 模式）
- ext-redis（Redis 缓存驱动）

## 文档

完整文档请参阅 [PHPFrame 文档](https://github.com/phpframe-project/phpframe/tree/main/docs)。

## License

MIT
