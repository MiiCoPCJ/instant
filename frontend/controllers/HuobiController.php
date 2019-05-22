<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;

class HuobiController extends Controller
{
    //ws
    public function WS()
    {
        return $this->View('huobi');
    }


}
