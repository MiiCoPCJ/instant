<?php

namespace frontend\controllers;
use hzfw\web\Controller;

class NoticeController extends Controller
{
    private $db;

    public function __construct(\DefaultDb $db){
        $this->db = $db;
    }
    //比较3分钟数据，获取涨幅
    public function Compare(){
        $sql = 'select max(`time`) as `time`,`price` from `eos`';
        $last = $this->db->QueryOne($sql);

        $time = $last['time'];
        $
        $preTime = $time-180;

        $pre = $this->db->QueryOne('select `time`,`price` from `eos` where `time` = '.$preTime);
        //不存在具体时间戳数据处理
        if(empty($pre)){
            $pre = $this->db->QueryOne('select max(`time`) as `time`,`price` from `eos` where `time` <= '.$preTime);
        }
        var_dump($pre);
    }
}
