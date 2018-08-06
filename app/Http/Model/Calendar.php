<?php
/**
 * Created by PhpStorm.
 * User: 32-
 * Date: 2018/8/6
 * Time: 23:17
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class Calendar
{
    const KEY = '22188226c8b09faa84570c37c8e549bc';

    //根据传入日期返回当天详细信息
    public function getDayInfo($date)
    {
        $url = 'http://v.juhe.cn/calendar/day';
        $params = [
            'key' => self::KEY,
            'date' => $date
        ];
        $result = Curl::get($url, $params);

        if (isset($result['error_code']) && $result['error_code'] === 0) {
            return $result['result']['data'];
        }
        return false;
    }

    //根据传入的月份返回近期的假期列表
    public function getHolidayInfo($yearMonth)
    {
        $url = 'http://v.juhe.cn/calendar/month';
        $params = [
            'key' => self::KEY,
            'year-month' => $yearMonth
        ];

        $result = Curl::get($url, $params);

        if (isset($result['error_code']) && $result['error_code'] === 0) {
            $info = $result['result']['data'];
            $data = [];
            foreach ($info['holiday_array'] as $holiday) {
                foreach ($holiday['list'] as $item) {
                    $data[$item['date']] = [
                        'is_holiday' => $item['status'],
                        'holiday_name' => $holiday['name']
                    ];
                }
            }
            return $data;
        }
        return false;
    }

    public function delete($dates)
    {
        return DB::table('calendar')
            ->where('date', 'in', $dates)
            ->delete();
    }

    public function insert($data)
    {
        return DB::table('calendar')->insert($data);
    }
}