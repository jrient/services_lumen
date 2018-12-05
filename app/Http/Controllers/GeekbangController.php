<?php
/**
 *
 * 拉取极客时间的已购买课程
 * User: 32-
 * Date: 2018/11/17
 * Time: 23:49
 */

namespace App\Http\Controllers;

use App\Http\Model\Curl;
use App\Http\Model\Geekbang;
use App\Http\Model\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GeekbangController extends Controller
{
    const INDEX_CACHE_TOKEN = 'geekbang_index_cache_token';

    public $viewParams = [
        'title' => 'Geek时间分享',
        'keywords' => 'jrient geekbang service',
        'description' => 'jrient geekbang service'
    ];

    public function index(Request $request)
    {
        $articleId = $request->get('id');
        $bid = $request->get('bid');
        $data = $this->getIndexCache();
        $model = new Geekbang();
        if (empty($data)) {
            $result = $model->getBookList();
            $articleResult = $model->getArticleList();
            $articleData = array();
            foreach ($articleResult as $item) {
                $articleData[$item->book_id][] = $item;
            }
            $data = array();
            foreach($result as $item) {
                $item->article_info = $articleData[$item->book_id];
                $data[$item->category][] = $item;
            }
            $this->setIndexCache($data);
        }
        $articleInfo = $model->getArticleInfo($articleId);
        $this->viewParams['data'] = $data;
        $this->viewParams['articleInfo'] = $articleInfo;
        $this->viewParams['articleId'] = $articleId;
        $this->viewParams['bid'] = $bid;
        $this->display('Geekbang/index');
    }

    public function article(Request $request)
    {
        $articleId = $request->get('id');
        $model = new Geekbang();
        $articleInfo = $model->getArticleInfo($articleId);
        Response::json(0, $articleInfo);
    }

    public function provider(Request $request)
    {
        $this->display('Geekbang/provider');
    }

    public function cookie(Request $request)
    {
        $cookie = $request->post('cookie');
        $geekbangModel = new Geekbang();
        $geekbangModel->insertCookie($cookie);
        Response::json(0);
    }

    public function updateData()
    {
        set_time_limit(0);
        $model = new Geekbang();
        $model->updateCookieByUserPass();
        $providerList = $model->getProviderList();
        if (empty($providerList)) {
            Response::json(-1);
        }
        foreach ($providerList as $item) {
            $model->cookie = $item->cookie;
            //验证cookie的有效性
            if ($model->validCookie()) {
                //获取书籍列表
                $model->updateBookList();
            } else {
                $model->setProviderStatus($item->id, 0);
            }

        }
        $this->delIndexCache();
        Response::json(0);
    }

    public function clearCache()
    {
        $this->delIndexCache();
    }

    # ================

    /**
     * 设置书籍目录缓存
     * @param $data
     */
    private function setIndexCache($data)
    {
        Redis::set(self::INDEX_CACHE_TOKEN, json_encode($data));
        Redis::expire(self::INDEX_CACHE_TOKEN, 24*3600);
    }

    private function getIndexCache()
    {
        $data = Redis::get(self::INDEX_CACHE_TOKEN);
        return empty($data) ? array() : json_decode($data, false);
    }

    private function delIndexCache()
    {
        Redis::del(self::INDEX_CACHE_TOKEN);
    }
}
