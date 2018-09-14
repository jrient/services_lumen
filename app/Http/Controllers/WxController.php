<?php

namespace App\Http\Controllers;

use App\Http\Model\Calendar;
use App\Http\Model\Response;
use Illuminate\Http\Request;
use TheSeer\Tokenizer\Exception;

class WxController extends Controller
{


    public function message(Request $request)
    {
        $postData = $request->getContent();
        $postObj = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);

//        $toUserName = $postData['ToUserName'];
//        $fromUserName = $postData['FromUserName'];
//        $msgType = $postData['MsgType'];
//        $msgId = $postData['MsgId'];

    }

    /**
     * 验证服务器
     * @param Request $request
     */
    public function index(Request $request)
    {
        try {
            $requestParams = $request->all();
            $signature = $requestParams['signature'];
            $timestamp = $requestParams['timestamp'];
            $nonce     = $requestParams['nonce'];
            $echoStr   = $requestParams['echostr'];
            $token = config('wx.token');

            $list = array($token, $timestamp, $nonce);
            sort($list);
            $list = implode('', $list);
            $hashCode = sha1($list);

            if ($hashCode == $signature) {
                echo $echoStr;
            } else {
                echo '';
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
