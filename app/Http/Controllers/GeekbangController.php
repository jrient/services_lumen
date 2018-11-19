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

class GeekbangController extends Controller
{
    public function index(Request $request)
    {
        $model = new Geekbang();
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
        $this->viewParams['data'] = $data;
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
        Response::json(0);
    }
}
