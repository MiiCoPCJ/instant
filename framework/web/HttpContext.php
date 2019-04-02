<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class HttpContext extends BaseObject
{
    /**
     * HTTP请求
     * @var HttpRequest
     */
    public $request = null;
    
    /**
     * HTTP响应
     * @var HttpResponse
     */
    public $response = null;
    
    /**
     * HTTP上下文
     * @param HttpRequest $request
     * @param HttpResponse $response
     */
    public function __construct(
        HttpRequest $request, HttpResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
