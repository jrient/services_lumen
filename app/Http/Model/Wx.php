<?php
/**
 * Created by PhpStorm.
 * User: 32-
 * Date: 2018/9/17
 * Time: 23:50
 */

namespace App\Http\Model;

class Wx
{
    public function buildTextMsg($toUsername, $fromUsername, $time, $content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        return sprintf($textTpl, $toUsername, $fromUsername, $time, 'text', $content);
    }
}