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
    public $key;

    public function __construct()
    {
        $this->key = config('calendar.key');
    }

    public function dayInfo($date)
    {
        return DB::table('calendar')
//            ->select(['date', 'weekday', 'animals_year', 'suit', 'avoid', 'lunar', 'lunar_year', 'holiday_name'])
            ->where(['date' => $date])
            ->limit(1)
            ->first();
    }

    //根据传入日期返回当天详细信息 从数据源查询
    public function getDayInfoFromSource($date)
    {
        $date = date('Y-n-j', strtotime($date));
        $url = 'http://v.juhe.cn/calendar/day';
        $params = [
            'key' => $this->key,
            'date' => $date
        ];
        $result = Curl::get($url, $params);

        if (isset($result['error_code']) && $result['error_code'] === 0) {
            return $result['result']['data'];
        }
        return false;
    }

    //根据传入的月份返回近期的假期列表 从数据源查询
    public function getHolidayInfoFromSource($yearMonth)
    {
        $url = 'http://v.juhe.cn/calendar/month';
        $params = [
            'key' => $this->key,
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
            ->whereIn('date', $dates)
            ->delete();
    }

    public function insert($data)
    {
        return DB::table('calendar')->insert($data);
    }

    public function formatData($result)
    {
        return [
            'date' => $result['date'],
            'weekday' => $result['weekday'],
            'animals_year' => $result['animalsYear'],
            'suit' => isset($result['suit']) ? $result['suit'] : '',
            'avoid' => isset($result['avoid']) ? $result['avoid'] : '',
            'lunar' => $result['lunar'],
            'lunar_year' => $result['lunarYear'],
            'is_holiday' => isset($result['holiday']) ? 1 : 0,
            'holiday_name' => isset($result['holiday']) ? $result['holiday'] : '',
            'year' => date('Y', strtotime($result['date'])),
            'month' => date('n', strtotime($result['date'])),
            'day' => date('j', strtotime($result['date'])),
        ];
    }
}