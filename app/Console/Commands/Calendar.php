<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Calendar extends Command
{
    //每日限额100 以下用作脚本用
    const DAILY_NUM = 90;

    public $num = 0;
    public $status = 0; //0未用完 1已用完

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        $start = date("Y", strtotime("+5 year"));
        $end   = date("Y", strtotime("+5 year"));

        for ($i = $start; $i <= $end; $i++) {
            $status = $this->saveData($i);
            if (!$status) {
                echo "每日限额已用完，结束\n";
                exit;
            }
        }

    }

    private function saveData($year)
    {
        $calendar = new \App\Http\Model\Calendar();
        $data = [];
        for ($m = 1; $m <= 12; $m ++) {
            //查询每日的信息
            echo "\n 查询每日信息:\n";
            $endDay = date('d', strtotime("{$year}-{$m}-1 +1 month -1 day"));
            for ($d = 1; $d <= $endDay ; $d ++) {
                $thisDate = "{$year}-{$m}-{$d}";
                echo $thisDate;
                $haveInfo = $calendar->dayInfo($thisDate);
                if (empty($haveInfo)) {
                    $result = $calendar->getDayInfoFromSource("$thisDate");
                    if (empty($result)) {
                        $this->status = 1;
                        echo " 获取不到信息\n";
                        break;
                    } else {
                        echo " 查询成功\n";
                        //记录使用限额次数
                        $this->num++;
                        if ($this->num >= self::DAILY_NUM) {
                            $this->status = 1;
                            break;
                        }
                    }
                    $data[$result['date']] = $calendar->formatData($result);
                } else {
                    echo " 已有记录\n";
                }
            }

            $dateLists = [];
            $insertData = [];
            foreach ($data as $key => $item) {
                $dateLists[] = $key;
                $insertData[] = $item;
            }

            //删除原始数据
            echo "删除原始数据";
            echo $calendar->delete($dateLists);
            echo "\n";

            //新增数据
            echo "新增数据";
            if ($calendar->insert($insertData)) {
                echo count($insertData);
            }
            echo "\n";

            if ($this->status === 1) {
                return false;
            }
        }
        return true;
    }

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
}