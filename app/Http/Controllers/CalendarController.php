<?php
/**
 *  功能: 万年历 提供节假日查询，日历查询等功能
 */
namespace App\Http\Controllers;

use App\Http\Model\Calendar;
use App\Http\Model\Response;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    const KEY = '';


    /**
     * 查询单个日期信息
     * @param Request $request
     * @param null $date
     */
    public function date(Request $request, $date = null)
    {
        $time = strtotime($date);
        if (empty($time)) {
            //失败 请求参数错误
            Response::json(Response::INVALID_PARAMETER);
        }
        $date = date('Y-m-d', $time);
        $calendar = new Calendar();
        $result = $calendar->dayInfo($date);
        $result = json_decode(json_encode($result),true);

        if (empty($result)) {
            //回源查询
            $result = $calendar->getDayInfoFromSource($date);
            if (empty($result)) {
                //失败 没有查询到数据
                Response::json(Response::NO_DATA);
            }
            $result = $calendar->formatData($result);
            $calendar->insert($result);
        }

        Response::json(Response::SUCCESS,[
            'date' => $result['date'],
            'weekday' => $result['weekday'],
            'animals_year' => $result['animals_year'],
            'suit' => $result['suit'],
            'avoid' => $result['avoid'],
            'lunar' => $result['lunar'],
            'lunar_year' => $result['lunar_year'],
            'holiday' => $result['holiday_name']
        ]);
    }
}