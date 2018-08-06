<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Calendar extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'calendar:get';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = 'get calendar data';

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set("display_errors", "On");
        error_reporting(E_ALL);
        $year = date('Y');

        $calendar = new \App\Http\Model\Calendar();
        $data = [];
        for ($m = 1; $m <= 12; $m ++) {
            //查询每日的信息
            echo "\n 查询每日信息:";
            $endDay = date('d', strtotime("{$year}-{$m}-1 +1 month -1 day"));
            for ($d = 1; $d <= $endDay ; $d ++) {
                echo "{$year}-{$m}-{$d}\n";
                $result = $calendar->getDayInfo("{$year}-{$m}-{$d}");
                $data[$result['date']] = [
                    'date' => $result['date'],
                    'weekday' => $result['weekday'],
                    'animals_year' => $result['animalsYear'],
                    'suit' => $result['suit'],
                    'avoid' => $result['avoid'],
                    'lunar' => $result['lunar'],
                    'lunar_year' => $result['lunarYear'],
                    'is_holiday' => isset($result['holiday']) ? 0 : 1,
                    'holiday_name' => isset($result['holiday']) ? $result['holiday'] : '',
                    'year' => date('Y', strtotime($result['date'])),
                    'month' => date('n', strtotime($result['date'])),
                    'day' => date('j', strtotime($result['date'])),
                ];
            }

            //查询假日信息
//            echo "\n 查询假日信息:";
//            $holidayInfo = $calendar->getHolidayInfo("{$year}-{$m}");
//            if (!empty($holidayInfo)) {
//                foreach ($holidayInfo as $date => $item) {
//                    if (date('Y-n', strtotime($date)) !== "{$year}-{$m}") {
//                        continue;
//                    }
//                    $data[$date] = isset($data[$date]) ? array_merge($data[$date], $item) : $item;
//                }
//            }

            $dateLists = [];
            $insertData = [];
            foreach ($data as $key => $item) {
                $dateLists[] = $key;
                $insertData[] = $item;
            }

            //删除原始数据
            echo $calendar->delete($dateLists);
            echo "\n";

            //新增数据
            echo $calendar->insert($insertData);
            echo "\n";
        }
    }
}