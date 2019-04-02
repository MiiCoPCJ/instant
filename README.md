# hzfw
PHP依赖注入强类型框架 (PHP dependency injection strongly typed framework)  
轻量级MVC框架 (Lightweight MVC framework)

## 要求
最低 PHP7.1

## 基本目录
站点目录 hzfw/frontend/web  
配置文件 hzfw/frontend/config/main.php  
配置文件 hzfw/frontend/config/config.json  
控制器 hzfw/frontend/controllers  
模型 hzfw/frontend/models  
视图  hzfw/frontend/views  
小部件 hzfw/frontend/viewcomponents  
过滤器 hzfw/frontend/filters

## URL重写(urlrewrite)

```
Apache -------------------------------

RewriteEngine on  

# if a directory or a file exists, use it directly  
RewriteCond %{REQUEST_FILENAME} !-f  
RewriteCond %{REQUEST_FILENAME} !-d  

# otherwise forward it to index.php  
RewriteRule . index.php  

Nginx -------------------------------

location / {
	if (!-e $request_filename){
		rewrite ^/(.*) /index.php last;
	}
}

IIS -------------------------------

<rule name="RewriteUserFriendlyURL" enabled="true" stopProcessing="true">
	<match url=".*" />
	<conditions>
		<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
		<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
	</conditions>
	<action type="Rewrite" url="index.php" />
</rule>
```

## 配置说明
```
{
    "DefaultDb": {
        "Dsn": "mysql:host=127.0.0.1;port=3306;dbname=db",
        "UserName": "root",
        "PassWord": "passwd"
    },
	"Mvc": {
		//设置默认编码
		"Charset": "utf-8",

		//设置控制器命名空间
		"ControllerNamespace": "frontend\\controllers",
    
		//设置小部件命名空间
		"ViewComponentNamespace": "frontend\\viewcomponents",
    
		//设置视图目录
		"ViewPath": "frontend/views",
    
		//设置默认错误页面
		"Error": "Site/Error"
	},
	"Route": {
		"Rules":[
			{
				//路由名称
				"Name": "Index",
        
				//路由模板
				"Template": "",
        
				//路由默认控制器和动作名称
				"Defaults": {"Controller": "Site", "Action": "Index"}
			},
			{
				//变量使用 <>符号表示
				//例：/test1/asd/
				"Name": "Test1",
				"Template": "test1/<name>/",
				"Defaults": {"Controller": "Site", "Action": "Test1"}
			},
			{
				//变量支持正则匹配
				//例：/test2/100/
				"Name": "Test2",
				"Template": "test2/<id:\\d+>/",
				"Defaults": {"Controller": "Site", "Action": "Test2"}
			},
			{
				//默认路由
				//例：/site/index
				//例：Action = TestTest, /site/test-test
				"Name": "Default",
				"Template": "<Controller>/<Action>",
				"Defaults": {}
			}
		]
	}
}

```

## 入口文件

frontend/web/index.php  

```
<?php

error_reporting(E_ALL);

//是否调试模式（只是一个标记，具体功能需要自己处理）
defined('HZFW_DEBUG') or define('HZFW_DEBUG', false);

//初始化框架
require_once(__DIR__ . '/../../framework/init.php');

//配置
require_once(__DIR__ . '/../config/main.php');
```

##  配置

frontend/config/main.php  

```
<?php

use hzfw\web\Mvc;
use hzfw\web\Config;
use frontend\filter\TestFilter;

//默认数据库
class DefaultDb extends \hzfw\base\Database {
}

//配置文件
//把 Config类 添加到服务
hzfw::AddServiceSingleton(Config::ClassName(), function () {
	return new Config(json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true));
});

//数据库操作
//把 DefaultDb类 添加到服务
hzfw::AddServiceSingleton(DefaultDb::ClassName(), function (Config $config) {
	return new DefaultDb($config->DefaultDb->Dsn, $config->DefaultDb->UserName, $config->DefaultDb->PassWord);
});

//其他自己编写的类
//在这里继续添加到服务
//需要注意的是，各个服务类不能相互初始化对方，会导致死循环

//AddServiceSingleton (只创建一次实例)
//AddServiceTransient (每次都新创建实力)
//hzfw::GetService() (手动方式获取服务实例)
//添加到服务的类。可以在 function __construct() 的时候自动获取实例
//例如 function __construct(DefaultDb $db)

//MVC 初始化
$mvc = Mvc::Init();

//添加过滤器（可以对输入输出的数据进行修改）
$mvc->AddFilter(TestFilter::ClassName());

//运行
$mvc->Run();
```

## MVC命名规则

控制器规则：控制器名称 + Controller.php  
视图规则：控制器名称/动作名称.php  
需要注意大小写  

## 路由

配置规则看上面  
路由参数和get参数可以绑定到控制器内的动作参数中直接获取
可在控制器 和 视图内使用下面命令  

```
//根据路由创建 url地址
$this->route->CreateUrl();
$this->route->CreateAbsoluteUrl();
```

## 控制器

控制器规则：控制器名称 + Controller.php  
frontend/controllers/SiteController.php  

```
<?php

namespace frontend\controllers;
use hzfw\web\Controller;

class SiteController extends Controller
{
	//异常
	public function Error(?\Throwable $e = null)
	{
		//MVC运行后的异常将会捕获到这里处理
		//默认会调用上面配置的错误页面。页面显示自己处理。
		return $this->View('', $e);
	}

	//首页
	public function Index()
	{
		//根据上面配置，首页
		return $this->View();
	}
}
```

## 小部件

frontend/views/Components  
和控制器类似，但由视图内调用。  

## 视图

视图规则：控制器名称/动作名称.php  
视图内可用 $model 取得传入的数据  

必备文件  
frontend/views/Layouts/Main.php  
View() 调用视图时 会载入这个文件  
ViewPartial() 则忽略  

Main.php
```
<?php use \hzfw\base\Encoding; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="/css/site.css">
    <title><?= Encoding::HtmlEncode($this->title) ?></title>
    <?= $this->head ?>
</head>
<?= $this->beginPage ?>
<body>
<?= $this->beginBody ?>
<?= $content ?>
<script src="/js/site.js"></script>
<?= $this->endBody ?>
</body>
<?= $this->endPage ?>
</html>

```

## 数据库操作例子

定义Model  

```
<?php

namespace frontend\models;
use hzfw\web\Model;

class UserModel extends Model
{
    public $id;
    public $name;
    public $password;
}

```

读取数据  
```
//使用 hzfw::GetService() 或 __construct 获取 db实例
$model = UserModel::Parse($db->QueryOne("SELECT `id`, `name`, `password` FROM `user` WHERE `name` = :name", [
    ":name" => "xxxx"
]));

```
