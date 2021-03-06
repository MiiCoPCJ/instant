<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class HttpRequest extends BaseObject
{    
    /**
     * 请求头
     * @var array
     */
    private $header = [];
    
    /**
     * Cookie
     * @var array
     */
    private $cookie = [];
    
    /**
     * Query参数
     * @var array
     */
    private $query = [];
    
    /**
     * 路由参数
     * @var array
     */
    private $route = [];
    
    /**
     * Body参数
     * @var array
     */
    private $body = [];
    
    /**
     * JSON数据
     * @var array|null
     */
    private $json = null;
    
    /**
     * 原始数据
     * @var string|null
     */
    private $raw = null;
    
    /**
     * 文件对象
     * @var array
     */
    private $file = [];
    
    /**
     * HTTP请求
     */
    public function __construct()
    {
        $this->query = $_GET;
        $this->cookie = $_COOKIE;
        $this->body = $_POST;
        
        foreach ($_SERVER as $name => $value)
        {
            if (0 === strncmp($name, 'HTTP_', 5)) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $this->header[$name] = $value;
            }
        }
        
        foreach ($_FILES as $name => $value)
        {
            if (is_array($value))
            {
                $len = count($value);
                $this->file[$name] = [];
                for($i = 0; $i < $len; $i++)
                {
                    $this->file[$name][] = new Upload([
                        'error' => $value['error'][$i],
                        'tmp_name' => $value['tmp_name'][$i],
                        'name' => $value['name'][$i],
                        'type' => $value['type'][$i],
                        'size' => $value['size'][$i],
                    ]);
                }
            }
            else
            {
                $this->file[$name] = new Upload($value);
            }
        }
    }
    
    /**
     * 获取远程IP
     * @return string
     */
    public function GetRemoteIP(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * 获取HOST
     * @return string
     */
    public function GetHost(): string
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    /**
     * 获取基础地址
     * @return string
     */
    public function GetBaseUrl(): string
    {
        return ($this->IsHttps() ? 'https://' : 'http://') . $this->GetHost();
    }
    
    /**
     * 获取当前路径地址
     * @return string
     */
    public function GetCurrenUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * 获取当前地址
     * @return string
     */
    public function GetCurrenUrl(): string
    {
        return $this->GetBaseUrl() . $this->GetCurrenUri();
    }
    
    /**
     * 获取谓词
     * @return string
     */
    public function GetMethod(): string
    {
        if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }
        
        if(isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }
        
        return 'GET';
    }
    
    /**
     * 获取HTTP版本
     * @return string
     */
    public function GetVersion(): string
    {
        return $_SERVER['SERVER_PROTOCOL'];
    }
    
    /**
     * 是否GET
     * @return bool
     */
    public function IsGet(): bool
    {
        return 'GET' === $this->GetMethod();
    }
    
    /**
     * 是否POST
     * @return bool
     */
    public function IsPost(): bool
    {
        return 'POST' === $this->GetMethod();
    }
    
    /**
     * 是否OPTIONS
     * @return bool
     */
    public function IsOptions(): bool
    {
        return 'OPTIONS' === $this->GetMethod();
    }
    
    /**
     * 是否HEAD
     * @return bool
     */
    public function IsHead(): bool
    {
        return 'HEAD' === $this->GetMethod();
    }
    
    /**
     * 是否DELETE
     * @return bool
     */
    public function IsDelete(): bool
    {
        return 'DELETE' === $this->GetMethod();
    }
    
    /**
     * 是否PUT
     * @return bool
     */
    public function IsPut(): bool
    {
        return 'PUT' === $this->GetMethod();
    }
    
    /**
     * 是否PATCH
     * @return bool
     */
    public function IsPatch(): bool
    {
        return 'PATCH' === $this->GetMethod();
    }
    
    /**
     * 是否HTTPS
     * @return bool
     */
    public function IsHttps(): bool
    {
        $is_https = isset($_SERVER['HTTPS']) && ('on' === strtolower($_SERVER['HTTPS']) || 0 !== (int)$_SERVER['HTTPS']);
        if(false === $is_https) $is_https = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        return $is_https;
    }
    
    /**
     * 是否AJax
     * @return bool
     */
    public function IsAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'];
    }
    
    /**
     * 是否Pjax
     * @return bool
     */
    public function IsPjax(): bool
    {
        return $this->IsAjax() && !empty($_SERVER['HTTP_X_PJAX']);
    }
    
    /**
     * 是否Flash
     * @return bool
     */
    public function IsFlash(): bool
    {
        $userAgent = $this->GetHeader('User-Agent', '');
        return stripos($userAgent, 'Shockwave') !== false || stripos($userAgent, 'Flash') !== false;
    }
    
    /**
     * 获取Query
     * @param string $name
     * @param string $defaultValue
     * @return string|NULL
     */
    public function GetQuery(string $name, ?string $defaultValue = null): ?string
    {
        return (isset($this->query[$name]) ? $this->query[$name] : $defaultValue);
    }
    
    /**
     * 获取Query
     * @param string $name
     * @param array $defaultValue
     * @return array|NULL
     */
    public function GetQueryArray(string $name, ?array $defaultValue = null): ?array
    {
        return (isset($this->query[$name]) ? $this->query[$name] : $defaultValue);
    }
    
    /**
     * 获取所有Query
     * @return array
     */
    public function GetQueryAll(): array
    {
        return $this->query;
    }
    
    /**
     * 获取Body参数
     * @param string $name
     * @param string $defaultValue
     * @return string|NULL
     */
    public function GetBody(string $name, ?string $defaultValue = null): ?string
    {
        return (isset($this->body[$name]) ? $this->body[$name] : $defaultValue);
    }
    
    /**
     * 获取Body参数
     * @param string $name
     * @param array $defaultValue
     * @return array|NULL
     */
    public function GetBodyArray(string $name, ?array $defaultValue = null): ?array
    {
        return (isset($this->body[$name]) ? $this->body[$name] : $defaultValue);
    }
    
    /**
     * 获取所有Body参数
     * @return array
     */
    public function GetBodyAll(): array
    {
        return $this->body;
    }
    
    /**
     * 获取原始Body
     * 当 enctype="multipart/form-data" 时无效
     * @return string|null
     */
    public function GetRawBody(): ?string
    {
        if (null === $this->raw) {
            if('POST' === $this->GetMethod() && 
                0 !== strncmp($this->GetContentType(), 'multipart/form-data;', 20)) {
                $this->raw = file_get_contents('php://input');
            }
        }
        
        return $this->raw;
    }
    
    /**
     *  获取Json
     * @return array|NULL
     */
    public function GetJson(): ?array
    {
        if (null === $this->json) {
            if('POST' === $this->GetMethod() &&
                0 === strncmp($this->GetContentType(), 'application/json;', 17)) {
                $this->json = json_decode(file_get_contents('php://input'), true);
            }
        }
        
        return $this->json;
    }
    
    /**
     * 获取内容类型
     * @return null|string
     */
    public function GetContentType(): ?string
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            return $_SERVER['CONTENT_TYPE'];
        }
        
        return $this->GetHeader('Content-Type');
    }
    
    /**
     * 获取标识
     * @return null|string
     */
    public function GetUserAgent(): ?string
    {
        return $this->GetHeader('User-Agent');
    }
    
    /**
     * 获取来源
     * @return string|NULL
     */
    public function GetOrigin(): ?string
    {
        return $this->GetHeader('Origin');
    }
    
    /**
     * 获取来路
     * @return string|NULL
     */
    public function GetReferrer(): ?string
    {
        return $this->GetHeader('Referer');
    }
    
    /**
     * 获取希望接收类型
     * @return string|NULL
     */
    public function GetAccept(): ?string
    {
        return $this->GetHeader('Accept');
    }
    
    /**
     * 获取希望接收语言
     * @return string|NULL
     */
    public function GetAcceptLanguage(): ?string
    {
        return $this->GetHeader('Accept-Language');
    }
    
    /**
     * 获取HTTP头
     * @param string $name
     * @param string $defaultValue
     * @return string|NULL
     */
    public function GetHeader(string $name, ?string $defaultValue = null): ?string
    {
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
        return isset($this->header[$name]) ? $this->header[$name] : $defaultValue;
    }
    
    /**
     * 获取全部HTTP头
     * @return array
     */
    public function GetHeaderAll(): array
    {
        return $this->header;
    }
    
    /**
     * 获取cookie
     * @param string $name
     * @param string $defaultValue
     * @return string|NULL
     */
    public function GetCookie(string $name, ?string $defaultValue = null): ?string
    {
        return isset($this->cookie[$name]) ? $this->cookie[$name] : $defaultValue;
    }
    
    /**
     * 获取cookie
     * @param string $name
     * @param array $defaultValue
     * @return array|NULL
     */
    public function GetCookieArray(string $name, ?array $defaultValue = null): ?array
    {
        return isset($this->cookie[$name]) ? $this->cookie[$name] : $defaultValue;
    }
    
    /**
     * 获取全部cookie
     * @return array
     */
    public function GetCookieAll(): array
    {
        return $this->cookie;
    }
    
    /**
     * 获取上传文件
     * @param string $name
     * @return Upload|NULL
     */
    public function GetUpload(string $name): ?Upload
    {
        return isset($this->file[$name]) ? $this->file[$name] : null;
    }
    
    /**
     * 获取上传文件
     * @param string $name
     * @return array|NULL
     */
    public function GetUploadArray(string $name): ?array
    {
        return isset($this->file[$name]) ? $this->file[$name] : null;
    }
    
    /**
     * 获取上传文件
     * @return array
     */
    public function GetUploadAll(): array
    {
        return $this->file;
    }
}
