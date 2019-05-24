<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;
use hzfw\web\Common;

date_default_timezone_set('Asia/Shanghai');

class OkexController extends Controller
{
    public function V1(string $type,int $size,string $since)
    {
        $func = new Common();

        $arr = array();
        $arr['type'] = $type;
        $arr['size'] = $size;
        $arr['since'] = $since;

        $since = strtotime($since)*1000;

        $url = "https://www.okex.me/api/v1/kline.do?symbol=btc_usdt&type=$type&size=$size&since=$since";
        $api = $func->request_get($url);
        $api = json_decode($api,true);

        $okex = array();
        foreach($api as $key=>$value){
          $time = date('Y-m-d H:i:s',$value[0]/1000);
          $open = (float)$value[1];
          $high = (float)$value[2];
          $low = (float)$value[3];
          $close = (float)$value[4];
          $volume = (string)$value[5];

          $p = -1;
          if($open<$close){
            $p = 1;
          }

          $okex[] = [$time,$open,$high,$low,$close,$volume,$p];
        }
        
        $arr['okex'] = [];
        if(!empty($okex)){
          $arr['okex'] = json_encode($okex);
        }


        return $this->View('okex',$arr);
    }

    /**
     * v3端口 k线最多获取历史前2000个
     */
    public function V3(int $granularity,string $start,string $end)
    {
        $func = new Common();

        $arr = array();
        $arr['granularity'] = $granularity;
        $arr['start'] = $start;
        $arr['end'] = $end;

        $s = strtotime($start);
        $b = 0;
        $e = strtotime($end);


        $okex = [];
        while(true){

          $split =  $func->splitData($granularity,$s,$e);
          if(empty($split)){
            break;
          }
          $okex = array_merge($okex,$split);

          $b = strtotime($split[count($split)-1][0]);
          $e = $b;
        }

        $arr['okex'] = [];
        if(!empty($okex)){
          $arr['okex'] = json_encode($okex);
        }

        return $this->View('okex',$arr);
    }





}
