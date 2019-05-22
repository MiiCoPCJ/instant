<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;

class OkexController extends Controller
{
    //ws
    public function WS()
    {
        return $this->View('okex');
    }


}
