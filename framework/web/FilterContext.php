<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class FilterContext extends BaseObject
{
    /**
     * HTTP上下文
     * @var HttpContext
     */
    public $httpContext = null;
    
    /**
     * 路由
     * @var Route
     */
    public $route = null;
    
    /**
     * 初始化
     */
    public function __construct(HttpContext $httpContext, Route $route)
    {
        $this->httpContext = $httpContext;
        $this->route = $route;
    }
}
