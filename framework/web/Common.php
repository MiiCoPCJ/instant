<?php

namespace hzfw\web;
use hzfw\base\BaseObject;

class Common extends BaseObject
{
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

    public function log_file($str,$seaslog=true,$fileName=false, $append=true):void
    {
      if($seaslog){
          $this->seaslog($str);
      }else{
        $path = __DIR__.'/../logs/';
        $file = $path.'logs.log';
        if($fileName){
          $file = $path.$fileName;
        }

        file_put_contents($file, $str."\r\n", $append?FILE_APPEND:0);

      }
    }

    public function seaslog($str):void
    {
      $path = __DIR__.'/../logs/';
      \SeasLog::setBasePath($path);
      \SeasLog::info($str);
    }


    public function wechatMSG($msg):void
    {
        $params = ['text'=>'Notice', 'desp'=>$msg];
        $url = 'https://sc.ftqq.com/SCU32622T9be47f58c96567829e4a694388624f435ba48f250340e.send';
        $this->request_post($url,$params);
    }

    public function phoneMSG():void
    {
        $url = 'https://www.wafeng.com/func/sms?telephone=15219127449&str=1'.mt_rand(10000, 99999);
        $result = $this->request_get($url);
    }
}
