<?php

namespace App\Http\Controllers;

use App\Http\Model\Calendar;
use App\Http\Model\Gallery;
use App\Http\Model\Response;
use App\Http\Model\Wx;
use Illuminate\Http\Request;
use TheSeer\Tokenizer\Exception;

class WxController extends Controller
{


    public function message(Request $request)
    {
        $postData = $request->getContent();
        $postObj = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (empty($postObj)) {
            return '';
        }

        $wxModel = new Wx();
        $GalleryModel = new Gallery();
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $createTime = intval($postObj->CreateTime);
        $msgType = $postObj->MsgType;
        $time = time();

        $request = '';
        if ($msgType === 'text') {
            // 文本消息处理
            $content = trim($postObj->Content);

        } elseif ($msgType == 'image') {
            // 图片处理
            $picUrl = trim($postObj->PicUrl);
            $status = $GalleryModel->insert($picUrl, date('Y-m-d H:i:s', $createTime), $fromUsername);
            if (!empty($picUrl) && $status) {
                $replyContent = '保存图片成功';
                $request = $wxModel->buildTextMsg($fromUsername, $toUsername, $time, $replyContent);
            }
        } else {

        }

        echo $request;
        exit;
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
