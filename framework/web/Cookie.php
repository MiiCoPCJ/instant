<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class Cookie extends BaseObject
{
    /**
     * 名称
     * @var string
     */
    public $name = '';
    
    /**
     * 值
     * @var string
     */
    public $value = null;
    
    /**
     * 域
     * @var string
     */
    public $domain = null;
    
    /**
     * 路径
     * @var string
     */
    public $path = null;
    
    /**
     * 超时（时间戳秒）
     * @var integer
     */
    public $expires = null;
    
    /**
     * 必须HTTPS
     * @var bool
     */
    public $secure = null;
    
    /**
     * 只能HTTP传输，禁止js读取
     * @var bool
     */
    public $httpOnly = null;
    
    /**
     * 初始化
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function __construct(string $name, ?string $value = null, ?int $expires = null, 
        ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->path = $path;
        $this->expires = $expires;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }
}
