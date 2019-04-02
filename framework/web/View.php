<?php

namespace hzfw\web;
use hzfw\base\BaseObject;
use hzfw;

//视图
class View extends BaseObject
{
    /**
     * 配置
     * @var Config
     */
    private $config = null;
    
    /**
     * 目录名称
     * @var string
     */
    private $dirName = '';
    
    /**
     * 文件名称
     * @var string
     */
    private $fileName = '';
    
	/**
	 * 标题
	 * @var string
	 */
	public $title = '';
	
	/**
	 * 头
	 * @var string
	 */
	public $head = '';
	
	/**
	 * 页开始
	 * @var string
	 */
	public $beginPage = '';
	
	/**
	 * 页结束
	 * @var string
	 */
	public $endPage = '';
	
	/**
	 * 正文开始
	 * @var string
	 */
	public $beginBody = '';
	
	/**
	 * 正文结束
	 * @var string
	 */
	public $endBody = '';
	
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
	 * 初始化
	 */
	public function __construct(string $dirName, string $fileName)
	{
	    $this->config = hzfw::GetService(Config::ClassName());
        $this->httpContext = hzfw::GetService(HttpContext::ClassName());
        $this->route = hzfw::GetService(Route::ClassName());
        $this->fileName = $fileName;
        $this->dirName = $dirName;
	}
	
	/**
	 * 视图
	 * @param string $viewName 视图名称或路径
	 * @param mixed $model
     * @return string
	 */
	public function View(string $viewName = '', $model = null): string
	{
	    $out = '';
	    ob_start();
	    ob_implicit_flush(0);
	    
	    extract(["content" => $this->ViewPartial($viewName, $model)], EXTR_OVERWRITE);
	    require(hzfw::$path . "/{$this->config->Mvc->ViewPath}/Layouts/Main.php");
	    
	    $out = ob_get_clean();
	    $out = false !== $out ? $out : '';
	    return $out;
	}
	
	/**
	 * 视图
	 * @param string $viewName 视图名称或路径
	 * @param mixed $model
     * @return string
	 */
	public function ViewPartial(string $viewName = '', $model = null): string
	{
	    if ('' === $viewName) {
	        $viewName = $this->fileName;
	    }
	    
	    if (0 !== strncmp($viewName, '/', 1)) {
	        $viewName = "/{$this->dirName}/{$viewName}";
	    }
	    
	    $out = '';
	    ob_start();
	    ob_implicit_flush(0);
	    
	    extract(["model" => $model], EXTR_OVERWRITE);
	    require(hzfw::$path . "/{$this->config->Mvc->ViewPath}{$viewName}.php");
	    
	    $out = ob_get_clean();
	    $out = false !== $out ? $out : '';
	    
	    return $out;
	}
	
	/**
	 * 小部件
	 * @param string $componentName 小部件名称
	 * @param mixed $model
     * @return string
	 */
	public function ViewComponent(string $componentName = '', $model = null): string
	{
	    $namespace = $this->config->Mvc->ViewComponentNamespace;
	    $obj = hzfw::NewService("\\{$namespace}\\{$componentName}ViewComponent");
	    $reflection = new \ReflectionClass($obj);
	    
	    $obj->component = null === $obj->component ? $componentName : $obj->component;
	    $obj->httpContext = null === $obj->httpContext ? $this->httpContext : $obj->httpContext;
	    $obj->route = null === $obj->route ? $this->route : $obj->route;
	    
	    return call_user_func_array([$obj, 'Run'], ["model" => $model]);
	}
	
	/**
	 * 创建标签
	 * @param string $tag
	 * @param array $attr
	 * @param string $value
	 * @param bool $closure
	 * @param bool $valuehtml
	 * @return string
	 */
	public function createTag(string $tag, array $attr = [], string $value = '', bool $closure = false, bool $valuehtml = false): string
	{
		$encode = function(string $content): string {
		    return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, $this->config->Mvc->Charset, true);
		};
		
		$ret = '';
		$ret .= '<' . $encode($tag);
		
		foreach ($attr as $name => $val) {
			$ret .= ' ' . $encode($name) . '="' . $encode($val) . '"';
		}
		
		if(false === $closure) {
			$ret .= ' />';
		}
		else
		{
			$ret .= '>';
			$ret .= $valuehtml ? $value : $encode($value);
			$ret .= '</' . $encode($tag) . '>';
		}
		
		return $ret;
	}
}
