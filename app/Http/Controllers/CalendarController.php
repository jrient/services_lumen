<?php
/**
 *  功能: 万年历 提供节假日查询，日历查询等功能
 */
namespace App\Http\Controllers;

use App\Http\Model\Curl;

class CalendarController extends Controller
{
    const KEY = '';


    /**
     *
     * 查询单个日期信息
     * @param null $date
     *
     */
    public function date($date = null)
    {
        $date = empty($date) ? date('Y-m-d') : date('Y-n-j', strtotime($date));

        $url = 'http://v.juhe.cn/calendar/day';

        $param = array(
            'date' => $date,
            'key' => '22188226c8b09faa84570c37c8e549bc'
        );

        var_dump(Curl::get($url, $param));

    }
}