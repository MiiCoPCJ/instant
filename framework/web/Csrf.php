<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class Csrf extends BaseObject
{
    /**
     * HTTP上下文
     * @var HttpContext
     */
    private $httpContext = null;
    
    /**
     * Cookie域
     * @var string
     */
    private $cookieDomain = '';
    
    /**
     * Cookie名
     * @var string
     */
    private $cookieName = '';
    
    /**
     * Csrf值
     * @var string
     */
    private $csrfValue = null;
    
    /**
     * 初始化
     * {
     *   "Csrf": {
     *     "CookieName": "_csrf",
     *     "CookieDomain": ""
     *   }
     * }
     */
    public function __construct(Config $config, HttpContext $httpContext)
    {
        $this->httpContext = $httpContext;
        $this->cookieName = $config->Csrf->CookieName;
        $this->cookieDomain = $config->Csrf->CookieDomain;
        $this->csrfValue = $httpContext->request->GetCookie($this->cookieName);
    }
    
    /**
     * 获取Token
     * @return string
     */
    public function GetToken(): string
    {
        if(null === $this->csrfValue)
        {
            $this->csrfValue = sha1(uniqid(uniqid('', true), true));
            $this->httpContext->response->AddCookie($this->cookieName, 
                $this->csrfValue, null, '/', '' === $this->cookieDomain ? null : $this->cookieDomain, null, true);
        }
        
        $randValue = substr(sha1(uniqid('', true)), 0, 16);
        return $randValue. sha1($randValue . $this->csrfValue);
    }
    
    /**
     * 验证Token
     * @param string $token
     * @return bool
     */
    public function ValidateToken(string $token): bool
    {
        $result = false;
        
        if(56 === strlen($token))
        {
            if (null !== $this->csrfValue)
            {
                $randValue = substr($token, 0, 16);
                $result = sha1($randValue . $this->csrfValue) === substr($token, 16);
            }
        }
        
        return $result;
    }
}
