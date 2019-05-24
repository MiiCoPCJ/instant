<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;
use hzfw\web\Common;

class HuobiController extends Controller
{
    //ws
    public function Pro(string $period,int $size)
    {
      $func = new Common();

      $arr = array();
      $arr['period'] = $period;
      $arr['size'] = $size;

      $url = "https://api.huobi.pro/market/history/kline?period=$period&size=$size&symbol=btcusdt";
      $api = $func->request_get($url);
      $api = json_decode($api,true);

      $huobi = array();
      foreach($api as $key=>$value){
        $time = date('Y-m-d H:i:s',$value['id']);
        $open = (float)$value['open'];
        $high = (float)$value['high'];
        $low = (float)$value['low'];
        $close = (float)$value['close'];
        $volume = (string)$value['vol'];

        $p = -1;
        if($open<$close){
          $p = 1;
        }

        $huobi[] = [$time,$open,$high,$low,$close,$volume,$p];
      }

      $arr['huobi'] = [];
      if(!empty($huobi)){
        $arr['huobi'] = json_encode($huobi);
      }


      return $this->View('huobi',$arr);
    }


}
