<?php

namespace App\Http\Model;

class Curl
{
    static public function post($url, $data, $header = array())
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
        curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST数据
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($ch);//接收返回信息
        $jsonData = json_decode($response, true);
        return empty($jsonData) ? $response : $jsonData;
    }

    static public function get($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        $response = curl_exec($ch);
        $jsonData = json_decode($response, true);
        return empty($jsonData) ? $response : $jsonData;
    }
}