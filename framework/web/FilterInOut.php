<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class FilterInOut extends BaseObject
{
    /**
     * 输入事件
     * @return bool 拦截为true
     */
    public function OnIn(FilterContext $context): bool
    {
        return false;
    }
    
    /**
     * 输出事件
     * @return bool 拦截为true
     */
    public function OnOut(FilterContext $context): void
    {
        
    }
}
