<?php

namespace frontend\filters;
use hzfw\web\FilterContext;
use hzfw\web\FilterInOut;

class TestFilter extends FilterInOut
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