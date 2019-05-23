<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;
use hzfw\web\Common;

date_default_timezone_set('Asia/Shanghai');

class OkexController extends Controller
{
    //ws
    public function WS(int $granularity,string $start,string $end)
    {
        $func = new Common();

        $arr = array();
        $arr['granularity'] = $granularity;
        $arr['start'] = $start;
        $arr['end'] = $end;

        $start = date('Y-m-d\TH:i:s\Z', strtotime($start) - date('Z'));
        $end = date('Y-m-d\TH:i:s\Z', strtotime($end) - date('Z'));

        $url = "https://www.okex.me/api/spot/v3/instruments/BTC-USDT/candles?granularity=$granularity&start=$start&end=$end";
        $api = $func->request_get($url);
        $api = json_decode($api,true);

        $okex = array();
        foreach($api as $value=>$key){
          $time = $value[0];
          $open = $value[1];
          $high = $value[2];
          $low = $value[3];
          $close = $value[4];
          $volume = $value[5];

          $p = -1;
          if($open<$close){
            $p = 1;
          }

          $okex[] = [$time,$open,$high,$low,$close,$volume];
        }
        var_dump($okex);die;

        $arr['okex'] = $api;

        return $this->View('okex',$arr);
    }


}
