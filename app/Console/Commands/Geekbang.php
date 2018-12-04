<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Model\Geekbang;

class GeekbangCommand extends Command
{
    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        $model = new Geekbang();
        $model->updateCookieByUserPass();
        $providerList = $model->getProviderList();
        if (empty($providerList)) {
            echo '出错';
        }
        foreach ($providerList as $item) {
            $model->cookie = $item->cookie;
            //验证cookie的有效性
            if (!$model->validCookie()) {
                $model->setProviderStatus($item->id, 0);
            }
            //获取书籍列表
            $model->updateBookList();
        }
    }

    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'geekbang:updateData';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = 'update data';
}
