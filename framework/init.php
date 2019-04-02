<?php

class UnknownClassException extends \Exception {}
class UnknownMethodException extends \Exception {}
class UnknownPropertyException extends \Exception {}
class UnknownParameterException extends \Exception {}
class UnknownTypeException extends \Exception {}

class hzfwServiceType
{
    const Transient = 0;
    const Singleton = 1;
}

class hzfwServiceItem
{
    public $type = 0;
    public $class = "";
    public $func = null;
    public $obj = null;
}

class hzfw
{
    //全局变量
    private static $alias = [];
    private static $classAlias = [];
    private static $services = [];
    
    /**
     * 项目目录
     * @var string
     */
    public static $path = '';
    
    /**
     * 获取别名
     * @param string $name
     * @return string|NULL
     */
    public static function GetAlias(string $name): ?string
    {
        return isset(self::$alias[$name]) ? self::$alias[$name] : null;
    }
    
    /**
     * 设置别名
     * @param string $name
     * @param string $value
     */
    public static function SetAlias(string $name, string $value): void
    {
        self::$alias[$name] = $value;
    }
    
    /**
     * 移除别名
     * @param string $name
     */
    public static function RemoveAlias(string $name): void
    {
        unset(self::$alias[$name]);
    }
    
    /**
     * 扩充别名文本
     * 特殊字符% 如果冲突 可写成 %%
     * $str = "%slias%/filename.txt"
     * @param string $str
     * @return string|NULL
     */
    public static function ExpandAliasString(string $str): ?string
    {
        try
        {
            $callback = function(array $matches): string
            {
                $value = self::getAlias($matches[1]);
                if(null === $value) throw new \Exception("unknown alias '{$matches[1]}'");
                return $value;
            };
            
            $result = preg_replace_callback('/%([^%]+?)%/', $callback, $str);
            $result = str_replace('%%', '%', $result);
            return $result;
        }
        catch (\Exception $e)
        {
            return null;
        }
    }
    
    /**
     * 获取类别名
     * $class = "foo\\bar"
     * @param string $class
     * @return string|NULL
     */
    public static function GetClassAlias(string $class): ?string
    {
        $result = null;
        $arr = explode('\\', $class);
        $classAlias = self::$classAlias;
        
        foreach ($arr as $item)
        {
            if(!isset($classAlias[$item])) {
                $result = null;
                break;
            }
            
            $result = $classAlias[$item]['value'];
            $classAlias = $classAlias[$item]['node'];
        }
        
        return $result;
    }
    
    /**
     * 设置类别名
     * $class = "foo\\bar"
     * $value = "aaa\\bbb\\ccc"
     * "foo\\bar\\zz" = "aaa\\bbb\\ccc\\zz"
     * @param string $class
     * @param string $value
     */
    public static function SetClassAlias(string $class, string $value): void
    {
        $arr = explode('\\', $class);
        $classAlias = &self::$classAlias;
        $len = count($arr);
        
        for($i = 0; $i < $len; $i++)
        {
            $item = $arr[$i];
            if(!isset($classAlias[$item]))
            {
                $classAlias[$item] = [
                    'node' => [],
                    'value' => null,
                ];
            }
            
            if($i + 1 === $len) {
                $classAlias[$item]['value'] = $value;
            }
            
            $classAlias = &$classAlias[$item]['node'];
        }
    }
    
    /**
     * 移除类别名
     * @param string $class
     */
    public static function RemoveClassAlias(string $class): void
    {
        $arr = explode('\\', $class);
        $classAlias = &self::$classAlias;
        $len = count($arr);
        
        for($i = 0; $i < $len; $i++)
        {
            $item = $arr[$i];
            if(!isset($classAlias[$item]))
            {
                $classAlias[$item] = [
                    'node' => [],
                    'value' => null,
                ];
            }
            
            if($i + 1 === $len) {
                $classAlias[$item]['value'] = null;
            }
            
            $classAlias = &$classAlias[$item]['node'];
        }
    }
    
    /**
     * 扩充类别名文本
     * @param string $class
     * @return string
     */
    static public function ExpandClassAliasString(string $class): string
    {
        $result = null;
        $arr = explode('\\', $class);
        $classAlias = self::$classAlias;
        $len = count($arr);
        
        for($i = 0; $i < $len; $i++)
        {
            $item = $arr[$i];
            if(!isset($classAlias[$item])) {
                break;
            }
            
            $result = $classAlias[$item]['value'];
            $classAlias = $classAlias[$item]['node'];
        }
        
        if(null !== $result)
        {
            for($j = $i; $j < $len; $j++) {
                $item = $arr[$j]; $result .= "\\{$item}";
            }
        }
        
        return (null !== $result ? $result : $class);
    }

    /**
     * 获取服务
     * $class = "foo\\bar"
     * @param string $class
     * @return null|object
     * @throws ReflectionException
     * @throws UnknownClassException
     * @throws UnknownParameterException
     */
    public static function GetService(string $class)
    {
        $result = null;
        $arr = self::GetServiceAll($class);
        
        if (null !== $arr && count($arr) > 0) {
            $result = $arr[count($arr) - 1];
        }
        
        return $result;
    }

    /**
     * 获取服务
     * $class = "foo\\bar"
     * @param string $class
     * @return array|null
     * @throws ReflectionException
     * @throws UnknownClassException
     * @throws UnknownParameterException
     */
    public static function GetServiceAll(string $class): ?array
    {
        $result = null;
        $services = isset(self::$services[$class]) ? self::$services[$class] : null;
        
        if (null !== $services)
        {
            $result = [];
            foreach ($services as $service)
            {
                if (hzfwServiceType::Transient === $service->type)
                {
                    $result[] = null !== $service->func ?
                        self::NewServiceFunc($service->class, $service->func) :
                        self::NewService($service->class);
                }
                else if (hzfwServiceType::Singleton === $service->type)
                {
                    if (null === $service->obj)
                    {
                        $service->obj = null !== $service->func ?
                            self::NewServiceFunc($service->class, $service->func) :
                            self::NewService($service->class);
                    }
                    $result[] = $service->obj;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 添加服务（每次创建）
     * @param string $class
     * @param callable $func
     * @throws UnknownClassException
     */
    public static function AddServiceTransient(string $class, ?callable $func = null): void
    {
        if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
            throw new UnknownClassException("class not exist '{$class}'");
        }
        
        if (!isset(self::$services[$class])) {
            self::$services[$class] = [];
        }
        
        $item = new hzfwServiceItem();
        {
            $item->obj = null;
            $item->func = $func;
            $item->class = $class;
            $item->type = hzfwServiceType::Transient;
        }
        
        self::$services[$class][] = $item;
    }
    
    /**
     * 添加服务（只创建一次）
     * @param string $class
     * @param callable $func
     * @throws UnknownClassException
     */
    public static function AddServiceSingleton(string $class, ?callable $func = null): void
    {
        if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
            throw new UnknownClassException("class not exist '{$class}'");
        }
        
        if (!isset(self::$services[$class])) {
            self::$services[$class] = [];
        }
        
        $item = new hzfwServiceItem();
        {
            $item->obj = null;
            $item->func = $func;
            $item->class = $class;
            $item->type = hzfwServiceType::Singleton;
        }
        
        self::$services[$class][] = $item;
    }

    /**
     * 创建对象
     * @param string $class
     * @return object
     * @throws ReflectionException
     * @throws UnknownClassException
     * @throws UnknownParameterException
     */
    public static function NewService(string $class)
    {
        $args = [];
        $reflection = new \ReflectionClass($class);
        
        //是否存在构造方法
        $reflectionMethod = $reflection->getConstructor();
        if (null !== $reflectionMethod)
        {
            //获取参数信息
            $reflectionParameters = $reflectionMethod->getParameters();
            foreach ($reflectionParameters as $reflectionParameter)
            {
                //获取类
                $parameterClass = $reflectionParameter->getClass();
                if (null === $parameterClass)
                {
                    //获取失败
                    $parameterName = $reflectionParameter->getName();
                    throw new UnknownParameterException("class '{$class}' parameter '{$parameterName}' not class");
                }
                
                //获取对象
                $parameterObj = self::GetService($parameterClass->getName());
                if (null === $parameterObj)
                {
                    //获取失败
                    $parameterName = $reflectionParameter->getName();
                    $parameterClassName = $parameterClass->getName();
                    throw new UnknownParameterException("class '{$class}' parameter '{$parameterName}' type '{$parameterClassName}' no service added");
                }
                else if (is_array($parameterObj))
                {
                    //使用最后一个
                    $args[] = $parameterObj[count($parameterObj) - 1];
                }
                else
                {
                    $args[] = $parameterObj;
                }
            }
        }
        
        //创建实例
        return $reflection->newInstanceArgs($args);
    }

    /**
     * 创建对象
     * @param string $class
     * @param callable $func
     * @return object
     * @throws ReflectionException
     * @throws UnknownClassException
     * @throws UnknownParameterException
     */
    public static function NewServiceFunc(string $class, callable $func)
    {
        $args = [];
        $reflectionFunction = new \ReflectionFunction($func);
        $reflectionParameters = $reflectionFunction->getParameters();
        
        foreach ($reflectionParameters as $reflectionParameter)
        {
            //获取类
            $parameterClass = $reflectionParameter->getClass();
            if (null === $parameterClass)
            {
                //获取失败
                $parameterName = $reflectionParameter->getName();
                throw new UnknownParameterException("class '{$class}' parameter '{$parameterName}' not class");
            }
            
            //获取对象
            $parameterObj = self::GetService($parameterClass->getName());
            if (null === $parameterObj)
            {
                //获取失败
                $parameterName = $reflectionParameter->getName();
                $parameterClassName = $parameterClass->getName();
                throw new UnknownParameterException("class '{$class}' parameter '{$parameterName}' type '{$parameterClassName}' no service added");
            }
            else if (is_array($parameterObj))
            {
                //使用最后一个
                $args[] = $parameterObj[count($parameterObj) - 1];
            }
            else
            {
                $args[] = $parameterObj;
            }
        }
        
        $obj = call_user_func_array($func, $args);
        if (!($obj instanceof $class))
        {
            $reflectionClass = new \ReflectionClass($obj);
            $reflectionClassName = $reflectionClass->getName();
            throw new UnknownClassException("return class '{$reflectionClassName}' not an instanceof a class '{$class}'");
        }
        
        return $obj;
    }
}

//类自动加载
hzfw::$path = dirname(dirname(__FILE__));
hzfw::SetClassAlias('hzfw', 'framework');
spl_autoload_register (function (string $class): void
{
    $filename = hzfw::ExpandClassAliasString($class);
    $filename = str_replace('\\', '/', hzfw::$path . '/' . $filename) . '.php';
    
    include $filename;
    if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
        throw new UnknownClassException("class not exist '{$class}', file '{$filename}'");
    }
});
