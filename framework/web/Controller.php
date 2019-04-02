<?php

namespace hzfw\web;
use hzfw\base\BaseObject;
use hzfw\base\FileStream;
use hzfw;

class Controller extends BaseObject
{
    /**
     * 控制器名称
     * @var string
     */
    public $controller = null;
    
    /**
     * 动作名称
     * @var string
     */
    public $action = null;
    
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
     * 视图
     * @param string $viewName 视图名称或路径
     * @param mixed $model
     */
    public function View(string $viewName = '', $model = null)
    {
        $httpContext = $this->httpContext;
        $view = new View($this->controller, $this->action);
        $httpContext->response->SetContentType('text/html');
        $httpContext->response->SetContent($view->View($viewName, $model));
    }
    
    /**
     * 视图
     * @param string $viewName 视图名称或路径
     * @param mixed $model
     */
    public function ViewPartial(string $viewName = '', $model = null)
    {
        $httpContext = $this->httpContext;
        $view = new View($this->controller, $this->action);
        $httpContext->response->SetContentType('text/html');
        $httpContext->response->SetContent($view->ViewPartial($viewName, $model));
    }
    
    /**
     * 返回JSON
     * @param mixed $content 内容
     * @param int $options json格式配置
     */
    public function Json($content, int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    {
        $httpContext = $this->httpContext;
        $httpContext->response->SetContent(json_encode($content, $options));
        $httpContext->response->SetContentType('application/json');
    }
    
    /**
     * 返回文件
     * @param string $content 内容
     * @param string $contentType（默认application/octet-stream）
     * @param int $rangeBegin 开始位置
     * @param int $rangeEnd 结束为止
     */
    public function File(string $content, ?string $contentType = null, ?int $rangeBegin = null, ?int $rangeEnd = null)
    {
        $httpContext = $this->httpContext;
        $response = $httpContext->response;
        
        if (null === $contentType) {
            $contentType = 'application/octet-stream';
        }
        
        $response->SetContent($content);
        $response->SetContentType($contentType);
        
        if(null !== $rangeBegin && null !== $rangeEnd)
        {
            if (null !== $rangeBegin && null === $rangeEnd) {
                $rangeEnd = strlen($content) - 1;
            }
            else if (null === $rangeBegin && null !== $rangeEnd) {
                $rangeBegin = 0;
            }
            
            $response->SetContentRange([$rangeBegin, $rangeEnd]);
        }
    }
    
    /**
     * 返回文件
     * @param string $fileName 文件名
     * @param string $contentType 文件类型（默认application/octet-stream）
     * @param int $rangeBegin 开始位置
     * @param int $rangeEnd 结束为止
     */
    public function FileStream(string $fileName, ?string $contentType = null, ?int $rangeBegin = null, ?int $rangeEnd = null)
    {
        $httpContext = $this->httpContext;
        $response = $httpContext->response;
        $stream = new FileStream($fileName, 'rb');
        
        if (null === $contentType) {
            $contentType = 'application/octet-stream';
        }
        
        $response->SetContentStream($stream);
        $response->SetContentType($contentType);
        
        if(null !== $rangeBegin && null !== $rangeEnd)
        {
            if (null !== $rangeBegin && null === $rangeEnd) {
                $rangeEnd = $stream->Size() - 1;
            }
            else if (null === $rangeBegin && null !== $rangeEnd) {
                $rangeBegin = 0;
            }
            
            $response->SetContentRange([$rangeBegin, $rangeEnd]);
        }
    }
    
    /**
     * 重定向
     * @param string $url
     * @param int $statusCode
     */
    public function Redirect(string $url, int $statusCode = 302)
    {
        $this->httpContext->response->Redirect($url, $statusCode);
    }
    
    /**
     * 重定向
     * @param string $routeName
     * @param array $params
     * @param int $statusCode
     */
    public function RedirectRoute(string $routeName, array $params = [], int $statusCode = 302)
    {
        $url = $this->route->CreateAbsoluteUrl($routeName, $params);
        $this->httpContext->response->Redirect($url, $statusCode);
    }
    
    /**
     * StatusCode 200
     */
    public function Ok()
    {
        $this->httpContext->response->SetStatusCode(200);
    }
    
    /**
     * StatusCode 400
     */
    public function BadRequest()
    {
        $this->httpContext->response->SetStatusCode(400);
    }
    
    /**
     * StatusCode 404
     */
    public function NotFound()
    {
        $this->httpContext->response->SetStatusCode(404);
    }
    
    /**
     * StatusCode 500
     */
    public function ServerError()
    {
        $this->httpContext->response->SetStatusCode(500);
    }
}
