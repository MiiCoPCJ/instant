<?php

namespace frontend\controllers;
use hzfw\web\Controller;

class CompareEOSController extends Controller
{
    //比较3分钟数据，获取涨幅
    public function Compare()
    {
        //$sql = 'select max(`time`) as `time`,`price` from `eos`';
        //$last = $this->db->QueryOne($sql);
        set_time_limit(0);
        do{
            $last = json_decode($this->redis->lPop('eos'),true);

            if($last==null||!$last){
              sleep(0.5);
              continue;
            }

            $time = $last['time'];
            $price = $last['price'];
            $preTime = $time-180;

            $pre = $this->db->QueryOne('select `time`,`price` from `eos` where `time` = '.$preTime);
            //不存在具体时间戳数据处理
            if(empty($pre)){
                $pre = $this->db->QueryOne('select max(`time`) as `time`,`price` from `eos` where `time` <= '.$preTime);
            }

            $comparePrice = $pre['price'];
            if(empty($comparePrice)){
              continue;
            }
            $ratio = round(($price-$comparePrice)/$price*100,8);

            //提醒
            //$msg = date('Y-m-d H:i:s',time()).' 3分钟涨幅: '.$ratio;
            $msg = 'price: '.$price.' comparePrice: '.$comparePrice.' || time: '.date('Y-m-d H:i:s',$time).' || '.date('Y-m-d H:i:s',$pre['time']).' || 3分钟涨幅: '.$ratio;


            if($ratio>=1.5){
                $this->func->log_file($msg,true);
                //$this->func->wechatMSG($msg);
                //$this->func->phoneMSG();
            }

            $this->func->log_file($msg);
        }while(true);

    }


}
