<?php
/**
 * Created by PhpStorm.
 * User: 32-
 * Date: 2018/8/8
 * Time: 23:53
 */

namespace App\Http\Model;

class Response
{
    const SUCCESS           = 0;
    const FAIL              = -1;
    const INVALID_PARAMETER = -4003;
    const NO_DATA           = -4004;

    public function message($statusCode)
    {
        $messages = [
            self::SUCCESS => 'success',
            self::FAIL => 'fail',
            self::INVALID_PARAMETER => 'invalid parameter',
            self::NO_DATA => 'data not fount',
            'default' => 'system error'
        ];

        return isset($messages[$statusCode]) ? $messages[$statusCode] : $messages['default'];
    }

    static public function json($statusCode, $data = [], $message = null)
    {
        $selfModel = new self();
        header('Content-type: application/json');
        echo json_encode([
            'status' => $statusCode,
            'message' => empty($message) ? $selfModel->message($statusCode) : $message,
            'data' => $data
        ]);
        exit;
    }
}