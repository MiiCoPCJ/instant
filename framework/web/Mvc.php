<?php

namespace hzfw\web;
use hzfw\base\BaseObject;
use hzfw;

class Mvc extends BaseObject
{
    /**
     * 配置
     * @var Config
     */
    private $config = null;

    /**
     * HTTP上下文
     * @var HttpContext
     */
    private $httpContext = null;

    /**
     * 路由
     * @var Route
     */
    private $route = null;

    /**
     * 过滤器
     * @var array
     */
    private $filter = [];

    /**
     * 初始化
     * {
     *   "Mvc": {
     *     "Charset": "utf-8",
     *     "ControllerNamespace": "frontend\\controllers",
     *     "ViewComponentNamespace": "frontend\\viewcomponents",
     *     "ViewPath": "frontend/views",
     *     "Error": "Site/Error"
     *   }
     * }
     */
    public function __construct(
        Config $config, HttpContext $httpContext, Route $route)
    {
        $this->config = $config;
        $this->httpContext = $httpContext;
        $this->route = $route;

        set_error_handler(function (int $errno, string $errstr,
            string $errfile, int $errline, array $errcontext) :bool {
            return true;
        }, E_ALL);

        set_exception_handler(function (\Throwable $ex): void {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true);
            //echo 'error occurred';
            echo $ex;
            exit();
        });
    }

    /**
     * Mvc初始化
     * @return self
     */
    public static function Init(): self
    {
        hzfw::AddServiceSingleton(Route::ClassName());
        hzfw::AddServiceSingleton(HttpRequest::ClassName());
        hzfw::AddServiceSingleton(HttpResponse::ClassName());
        hzfw::AddServiceSingleton(HttpContext::ClassName());
        hzfw::AddServiceSingleton(Mvc::ClassName());
        $obj = hzfw::GetService(Mvc::ClassName());
        return $obj;
    }

    /**
     * 添加过滤器
     * @param string $class
     */
    public function AddFilter(string $class): void
    {
        $this->filter[] = [$class, null];
    }

    /**
     * 运行
     */
    public function Run(): void
    {
        $stop = false;
        $route = $this->route;
        $response = $this->httpContext->response;

        try
        {
            //路由匹配失败，抛出404
            if ('' === $route->GetControllerName() || '' === $route->GetActionName()) {
                throw new HttpException(404, 'route matching failed');
            }

            //过滤器
            foreach ($this->filter as $v)
            {
                //判断继承 FilterInOut 类
                if ($this->Instanceof($v[0], FilterInOut::ClassName()))
                {
                    if (null === $v[1]) $v[1] = hzfw::NewService($v[0]);
                    $filterContext = new FilterContext($this->httpContext, $route);
                    if ($v[1]->OnIn($filterContext))
                    {
                        $stop = true;
                        break;
                    }
                }
            }

            if (false === $stop)
            {
                try
                {
                    //调用控制器
                    $this->CallAction(
                        $this->config->Mvc->ControllerNamespace,
                        $route->GetControllerName(),
                        $route->GetActionName());
                }
                catch (\UnknownClassException $e) {
                    throw new HttpException(404, $e->getMessage(), $e);
                }
                catch (\UnknownMethodException $e) {
                    throw new HttpException(404, $e->getMessage(), $e);
                }
                catch (\UnknownParameterException $e) {
                    throw new HttpException(404, $e->getMessage(), $e);
                }
                catch (\Throwable $e) {
                    throw $e;
                }

                //过滤器
                foreach ($this->filter as $v)
                {
                    //判断继承 FilterOut 类
                    if ($this->Instanceof($v[0], FilterInOut::ClassName()))
                    {
                        if (null === $v[1]) $v[1] = hzfw::NewService($v[0]);
                        $filterContext = new FilterContext($this->httpContext, $route);
                        $v[1]->OnOut($filterContext);
                    }
                }
            }
        }
        catch (\Throwable $e)
        {
            //错误处理
            for ($level = ob_get_level(); $level > 0; $level--) {
                if (false === @ob_end_clean()) {
                    ob_clean();
                }
            }

            $response->ClearHeader();
            $response->ClearCookie();
            $response->SetContent('');

            if (null !== ($stream = $response->GetContentStream())) {
                $response->SetContentStream(null);
                unset($stream);
            }

            $response->SetStatusCode(500);
            if($e instanceof HttpException) {
                $response->SetStatusCode($e->getCode());
            }

            $r = explode('/', $this->config->Mvc->Error);
            $this->CallAction($this->config->Mvc->ControllerNamespace, $r[0], $r[1], ['e' => $e]);
        }

        //发送头
        $this->SendHeader();

        //发送内容
        $this->SendContent();
    }

    /**
     * 发送头
     */
    private function SendHeader(): void
    {
        //响应信息
        $response = $this->httpContext->response;

        //设置状态码
        $version = $response->GetVersion();
        $statusCode = $response->GetStatusCode();
        $statusText = $response->GetStatusText();
        header("{$version} {$statusCode} {$statusText}", true);

        //HEADER设置
        foreach ($response->GetHeaderAll() as $k => $v)
        {
            if (is_array($v))
            {
                foreach ($v as $v2) {
                    header("{$k}: {$v2}", false);
                }
            }
            else
            {
                header("{$k}: {$v}", true);
            }
        }

        //COOKIE设置
        foreach ($response->GetCookieAll() as $v)
        {
            setcookie($v->name, $v->value, $v->expires, $v->path, $v->domain, $v->secure, $v->httpOnly);
        }
    }

    /**
     * 发送内容
     */
    private function SendContent(): void
    {
        //响应信息
        $response = $this->httpContext->response;

        //设置输出类型
        $contentType = $response->GetContentType();
        if (preg_match('/^text\/.*/', $contentType))
        {
            //文本类型
            $contentCharset = $response->GetContentCharset();
            header('' === $contentCharset ? "Content-Type: {$contentType}" : "Content-Type: {$contentType};charset={$contentCharset}", true);
        }
        else
        {
            //其他
            header("Content-Type: {$contentType}", true);
        }

        //输出内容
        $contentRange = $response->GetContentRange();
        if (null === $contentRange)
        {
            //直接返回
            $stream = $response->GetContentStream();
            if (null === $stream)
            {
                //输出内容
                echo $response->GetContent();
            }
            else
            {
                set_time_limit(0);
                $size = 1024 * 1204 * 8;

                while (!$stream->IsEof())
                {
                    //输出内容并强制刷新
                    echo $stream->Read($size);
                    flush();
                }

                unset($stream);
            }
        }
        else
        {
            //分段返回
            $response->SetStatusCode(206);
            $version = $response->GetVersion();
            $statusCode = $response->GetStatusCode();
            $statusText = $response->GetStatusText();
            header("{$version} {$statusCode} {$statusText}", true);

            list($begin, $end) = $contentRange;
            $stream = $response->GetContentStream();
            if (null === $stream)
            {
                //完整内容
                $content = $response->GetContent();
                $contentLength = strlen($content);

                //截取数据并返回
                header("Content-Range: bytes {$begin}-{$end}/{$contentLength}", true);
                $content = substr($content, $begin, $end - $begin + 1);
                echo $content;
            }
            else
            {
                set_time_limit(0);
                $size = 1024 * 1204 * 8;

                $contentLength = $stream->Size();
                header("Content-Range: bytes {$begin}-{$end}/{$contentLength}", true);

                //设置开始位置
                $stream->Seek($begin, SEEK_SET);

                //判断文件尾和指定范围
                while (!$stream->IsEof() && ($pos = $stream->Tell()) <= $end)
                {
                    //修正读取大小
                    if ($pos + $size > $end) {
                        $size = $end - $pos + 1;
                    }

                    //输出内容并强制刷新
                    echo $stream->Read($size);
                    flush();
                }

                unset($stream);
            }
        }
    }

    /**
     * 调用动作
     * @param string $namespace
     * @param string $controller
     * @param string $action
     * @param array $pars
     * @throws \UnknownMethodException
     * @throws \UnknownParameterException
     * @return mixed
     */
    private function CallAction(string $namespace, string $controller, string $action, array $pars = [])
    {
        $method = $action;
        $class = "\\{$namespace}\\{$controller}Controller";

        $obj = hzfw::NewService($class);
        $reflection = new \ReflectionClass($obj);

        if (!$reflection->hasMethod($method))
        {
            //方法不存在
            throw new \UnknownMethodException("class '{$class}' method '{$method}' not exist");
        }

        $obj->action = null === $obj->action ? $action : $obj->action;
        $obj->controller = null === $obj->controller ? $controller : $obj->controller;
        $obj->httpContext = null === $obj->httpContext ? $this->httpContext : $obj->httpContext;
        $obj->route = null === $obj->route ? $this->route : $obj->route;

        $params = [];
        $routes = $this->route->GetRouteAll();
        $querys = $this->httpContext->request->GetQueryAll();

        $reflectionMethod = $reflection->getMethod($method);
        if (!$reflectionMethod->isPublic())
        {
            //方法不是公开的
            throw new \UnknownMethodException("class '{$class}' method '{$method}' not public");
        }

        $reflectionParameters = $reflectionMethod->getParameters();
        foreach ($reflectionParameters as $reflectionParameter)
        {
            //获取参数名称和类型
            $parameterName = $reflectionParameter->getName();
            $parameterType = (string)$reflectionParameter->getType();

            //获取参数值
            $value = isset($pars[$parameterName]) ? $pars[$parameterName] : null;

            //从路由和GET参数填充
            if (null === $value) $value = isset($routes[$parameterName]) ? $routes[$parameterName] : null;
            if (null === $value) $value = isset($querys[$parameterName]) ? $querys[$parameterName] : null;
            if (null === $value && $reflectionParameter->isDefaultValueAvailable())
            {
                //使用默认值
                $value = $reflectionParameter->getDefaultValue();
                $params[$parameterName] = $value;
            }
            else if(null !== $value)
            {
                if ('' === $parameterType)
                {
                    //通用型
                    $params[$parameterName] = $value;
                }
                else if ('Throwable' === $parameterType)
                {
                    if (!($value instanceof \Throwable))
                    {
                        //不是异常类型
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no Throwable");
                    }
                    $params[$parameterName] = $value;
                }
                else if('string' === $parameterType)
                {
                    if (!is_string($value))
                    {
                        //不是字符串
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no string");
                    }
                    $params[$parameterName] = $value;
                }
                else if ('array' === $parameterType)
                {
                    if (!is_array($value))
                    {
                        //不是数组
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no array");
                    }
                    $params[$parameterName] = $value;
                }
                else if ('int' === $parameterType)
                {
                    if (!is_string($value) || 0 === preg_match('/^[+-]?([0-9]+)$/', $value))
                    {
                        //不是整数
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no int");
                    }
                    $params[$parameterName] = (int)$value;
                }
                else if ('float' === $parameterType)
                {
                    if (!is_string($value) || 0 === preg_match('/^[+-]?([0-9]+|[0-9]+[\.][0-9]+)$/', $value))
                    {
                        //不是小数
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no float");
                    }
                    $params[$parameterName] = (float)$value;
                }
                else if ('bool' === $parameterType)
                {
                    if (!is_string($value) || ('' !== $value && 0 === preg_match('/^(true|false|[01])$/', $value)))
                    {
                        //不是小数
                        throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' type no bool");
                    }
                    $params[$parameterName] = 'false' === $value ? false : ('true' === $value ? true : ('1' === $value ? true : false));
                }
            }
            else
            {
                //参数不存在
                throw new \UnknownParameterException("class '{$class}' parameter '{$parameterName}' not exist");
            }
        }

        return call_user_func_array([$obj, $method], $params);
    }

    /**
     * 和 instanceof 类似
     * @param string $className1
     * @param string $className2
     * @return bool
     */
    private function Instanceof(string $className1, string $className2): bool
    {
        try
        {
            $result = false;
            $reflection = new \ReflectionClass($className1);

            while(true)
            {
                $name = $reflection->getName();
                if ($name === $className2)
                {
                    $result = true;
                    break;
                }

                $reflection = $reflection->getParentClass();
                if (false === $reflection) break;
            }

            return $result;
        }
        catch (\Exception $e) {
            return false;
        }
    }
}
