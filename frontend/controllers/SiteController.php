<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;

class SiteController extends Controller
{
    //异常
    public function Error(?\Throwable $e = null)
    {
        return $this->View('', $e);
    }

    //首页
    public function Index()
    {
        //throw new \hzfw\web\HttpException(404);
        //$userContext = hzfw::GetService(UserContext::ClassName());
        //$data = $userContext->GetUser(1);
        return $this->View();
    }
    
    //测试1
    public function Test1(string $name)
    {
        return $this->View('', $name);
    }

    //测试2
    public function Test2(int $id)
    {
        return $this->View('Test2', $id);
    }

    //测试3
    //路由 /site/test-test3
    public function TestTest3()
    {
        $url = $this->route->CreateUrl('Default', ['Controller' => 'Site', 'Action' => 'TestTest3']);
        return $this->View('', $url);
    }
}
