<?php

namespace frontend\controllers;
use frontend\data\UserContext;
use hzfw\web\Controller;

class OkexController extends Controller
{
    //.me 非翻墙
    const okex_url = 'https://www.okex.me';
    private $db;

    public function __construct(\DefaultDb $db){
        $this->db = $db;
    }

    //获取okex的Api数据
    public function Data()
    {
      do{
        $url = self::okex_url.'/api/v1/ticker.do?symbol=eos_usdt';
        $data = $this->request_get($url);
        $data = json_decode($data,true);

        //判断获取是否错误
        if(!array_key_exists('error_code',$data)){
          $time = $data['date'];
          $price = $data['ticker']['last'];

          $sql = "insert into `eos`(`time`,`price`) values($time,$price)";

          $result = $this->db->Execute($sql);
        }
        sleep(1);
      }while(true);

    }


    public function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }


    public function request_get($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
