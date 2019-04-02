<?php

namespace hzfw\base;

class BaseObject
{
    /**
     * 获取类名
     * @return string
     */
    public static function ClassName(): string
    {
        return get_called_class();
    }
}
