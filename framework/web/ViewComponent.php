<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class ViewComponent extends BaseObject
{
    /**
     * 部件名称
     * @var string
     */
    public $component = null;
    
    /**
     * 上下文
     * @var HttpContext
     */
    public $httpContext = null;
    
    /**
     * 路由
     * @var Route
     */
    public $route = null;
    
    /**
     * 运行
     */
    public function Run($model): string
    {
        return '';
    }
    
    /**
     * 视图
     * @param string $viewName 视图名称或路径
     * @param mixed $model
     * @return string
     */
    public function View(string $viewName = '', $model = null): string
    {
        $view = new View('/Components/', $this->component);
        return $view->ViewPartial($viewName, $model);
    }
}
