<?php

namespace frontend\controllers;
use hzfw\web\Controller;

class EOSController extends Controller
{

    //获取okex的Api数据
    public function Data()
    {
      set_time_limit(0);
      do{
        $url = $this->okexURL.'/api/v1/ticker.do?symbol=eos_usdt';
        $data = $this->func->request_get($url);
        $data = json_decode($data,true);
        //判断获取是否错误
        if(!array_key_exists('error_code',$data)){
          $time = $data['date'];
          $price = $data['ticker']['last'];

          $sql = "insert into `eos`(`time`,`price`) values($time,$price)";

          $result = $this->db->Execute($sql);

          $last = ['time'=>$time, 'price'=>$price];
          //$this->Compare($last);
          $this->redis->lPush('eos',json_encode($last));
        }
        sleep(1);
      }while(true);
    }


    public function Compare($last)
    {
        if($last==null||!$last){
          return void;
        }

        $time = $last['time'];
        $price = $last['price'];
        $preTime = $time-60;

        $pre = $this->db->QueryOne('select `time`,`price` from `eos` where `time` = '.$preTime);
        //不存在具体时间戳数据处理
        if(empty($pre)){
            $pre = $this->db->QueryOne('select max(`time`) as `time`,`price` from `eos` where `time` <= '.$preTime);
        }
        if(empty($pre)){
          return void;
        }

        $comparePrice = $pre['price'];
        if(empty($comparePrice)){
          return void;
        }
        $ratio = round(($price-$comparePrice)/$price*100,8);

        //提醒
        $msg = 'price: '.$price.' comparePrice: '.$comparePrice.' || time: '.date('Y-m-d H:i:s',$time).' || '.date('Y-m-d H:i:s',$pre['time']).' || 3分钟涨幅: '.$ratio;

        if($ratio>=1.5){
            $this->func->log_file($msg,false);
            //$this->func->wechatMSG($msg);
            //$this->func->phoneMSG();
        }

        $this->func->log_file($msg);
    }

}
