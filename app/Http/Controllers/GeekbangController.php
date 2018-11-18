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
    const COOKIE = '_ga=GA1.2.804132098.1542436155; _gid=GA1.2.190921888.1542436155; GCID=bd87b56-e3ba976-651d7c4-615a349; PHPSESSID=1j6s2i7ht4am5trd808ues48d2; orderInfo={%22list%22:[{%22count%22:1%2C%22image%22:%22https://static001.geekbang.org/resource/image/5f/cd/5f197321896070a8ba9635b5b03764cd.jpg%22%2C%22name%22:%22MySQL%E5%AE%9E%E6%88%9845%E8%AE%B2%22%2C%22sku%22:100020801%2C%22price%22:{%22sale%22:6800}}]%2C%22invoice%22:false%2C%22app_id%22:3%2C%22cid%22:139%2C%22isFromTime%22:true%2C%22detail_url%22:%22https://time.geekbang.org/column/intro/139%22%2C%22utm_term%22:%22zeus8USXN%22}; GCESS=BAsCBAABBM3PEAACBNC271sJAQEHBHz36l8IAQMFBAAAAAAEBIBRAQADBNC271sGBFTIgEkMAQEKBAAAAAA-; SERVERID=97796d411bb56cf20a5612997f113254|1542469750|1542469741';

    private $cookie;

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