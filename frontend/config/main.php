<?php

use hzfw\web\Mvc;
use hzfw\web\Config;
use frontend\filters\TestFilter;
use frontend\data\UserContext;

//默认数据库
class DefaultDb extends \hzfw\base\Database {}

//配置
hzfw::AddServiceSingleton(Config::ClassName(), function () {
    return new Config(json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true));
});

//数据库
hzfw::AddServiceSingleton(DefaultDb::ClassName(), function (Config $config) {
    return new DefaultDb($config->DefaultDb->Dsn, $config->DefaultDb->UserName, $config->DefaultDb->PassWord);
});

//其他服务类
hzfw::AddServiceSingleton(UserContext::ClassName());

//MVC
$mvc = Mvc::Init();
$mvc->AddFilter(TestFilter::ClassName());
$mvc->Run();
