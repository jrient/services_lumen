<?php
/**
 * User: jrient
 * Date: 2018/11/18
 * Time: 15:13
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Geekbang
{
    const ARTICLE_TOKEN = 'geekbang_article_token';

    public $cookie;

    public $article_token;

    public function updateBookList()
    {
        $bookList = $this->getGeekBookList();
        if (empty($bookList)) {
            return false;
        }
        foreach ($bookList as $item) {
            $category = $item['title'] ?? '';
            if (empty($item['list'])) {
                continue;
            }
            foreach ($item['list'] as $i) {
                $bookData = [
                    'title' => $i['title'],
                    'article_count' => $i['extra']['article_count'] ?? 0,
                    'book_id' => $i['extra']['column_id'] ?? 0,
                    'author_name' => $i['extra']['author_name'] ?? '',
                    'sub_time' => $i['extra']['sub_time'] ?? 0,
                    'category' => $category
                ];
                $this->updateBook($bookData);
            }
        }
        return true;
    }

    public function updateBook($bookData)
    {
        $bookInfo = $this->getBookInfo($bookData['book_id']);
        //检查更新时间 判断是否要更新文章
//        if (!empty($bookInfo) && ($bookData['sub_time'] === $bookInfo->sub_time)) {
//            return true;
//        }
        if (empty($bookInfo)) {
            $this->saveBook($bookData);
        } else {
            DB::table('geekbang_book')->where(['id' => $bookInfo->id])->update($bookData);
        }
        $articleList = $this->getGeekArticleList($bookData['book_id']);
        if (empty($articleList)) {
            return true;
        }
        foreach($articleList as $item) {
            $articleData = [
                'book_id' => $bookData['book_id'],
                'article_id' => $item['id'],
                'article_title' => $item['article_title'],
                'article_summary' => $item['article_summary'],
                'sub_time' => $item['article_ctime']
            ];
            $this->updateArticle($articleData);
        }
        return true;
    }

    public function updateArticle($articleData)
    {
        $articleInfo = $this->getArticleInfo($articleData['article_id']);
        if (!empty($articleInfo) && ($articleData['sub_time'] === $articleInfo->sub_time)) {
            return true;
        }
        $geekArticleInfo = $this->getGeekArticleInfo($articleData['article_id']);
        if (empty($geekArticleInfo)) {
            Log::notice('未取到文章信息 id:' . $articleData['article_id'] . ' cookie:' . $this->cookie);
            return true;
        }
        $articleData = array_merge($articleData, [
            'audio_download_url' => $geekArticleInfo['audio_download_url'],
            'audio_time' => $geekArticleInfo['audio_time'],
            'content' => $geekArticleInfo['article_content'],
            'prev_id' => $geekArticleInfo['neighbors']['left']['id'] ?? 0,
            'next_id' => $geekArticleInfo['neighbors']['right']['id'] ?? 0,
            'prev_title' => $geekArticleInfo['neighbors']['left']['article_title'] ?? '',
            'next_title' => $geekArticleInfo['neighbors']['right']['article_title'] ?? ''
        ]);
        $this->article_token = $articleData['article_id'];
        $this->delArticleCache();
        if (empty($articleInfo)) {
            $this->saveArticle($articleData);
        } else {
            DB::table('geekbang_article')->where(['id' => $articleInfo->id])->update($articleData);
        }
        return true;
    }

    public function getBookList()
    {
        return DB::table('geekbang_book')->orderBy('sub_time', 'desc')->get()->toArray();
    }

    public function getArticleList()
    {
        return DB::table('geekbang_article')->orderBy('sub_time', 'desc')->get(['book_id', 'article_id','article_title', 'article_summary'])->toArray();
    }

    /**
     * 从本地数据库获取文章信息
     * @param $articleId
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getArticleInfo($articleId)
    {
        $this->article_token = $articleId;
        $data = $this->getArticleCache();
        if (empty($data)) {
            $data = DB::table('geekbang_article')->where(['article_id' => $articleId])->first();
            $this->setArticleCache($data);
        }
        return $data;
    }

    /**
     * 从本地数据库获取书籍信息
     * @param $bookId
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getBookInfo($bookId)
    {
        return DB::table('geekbang_book')->where(['book_id' => $bookId])->first();
    }

    public function saveBook($data)
    {
        return DB::table('geekbang_book')->insert($data);
    }

    public function saveArticle($data)
    {
        return DB::table('geekbang_article')->insert($data);
    }

    /**
     * 从极客时间接口获取书籍信息
     * @return array
     */
    public function getGeekBookList()
    {
        $url = 'https://time.geekbang.org/serv/v1/my/products/all';
        $data = Curl::post($url, array(), $this->getHttpHeader());
        return $data['data'] ?? array();
    }

    /**
     * 从极客时间接口获取章节目录
     * @param $bookId
     * @return array
     */
    public function getGeekArticleList($bookId)
    {
        $url = 'https://time.geekbang.org/serv/v1/column/articles';
        $postData = [
            'cid' => $bookId,
            'size' => 1000,
            'prev' => 0,
            'order' => 'newest',
            'sample' => true
        ];
        $data = Curl::post($url, json_encode($postData), $this->getHttpHeader());
        return $data['data']['list'] ?? array();
    }

    /**
     * 从极客时间接口获取文章信息
     * @param $articleId
     * @return array
     */
    public function getGeekArticleInfo($articleId)
    {
        $url = 'https://time.geekbang.org/serv/v1/article';
        $postData = [
            'id' => $articleId,
            'include_neighbors' => true
        ];
        $data = Curl::post($url, json_encode($postData), $this->getHttpHeader());
        return $data['data'] ?? array();
    }

    /**
     *  更新 设置了登陆信息的cookie
     */
    public function updateCookieByUserPass()
    {
        $data = DB::table('geekbang_provider')
            ->whereBetween('status', [-9, 0])
            ->where('username_password', '<>', '')
            ->get();
        foreach ($data as $item) {
            $userInfo = json_decode($item->username_password);
            if (empty($userInfo) || empty($userInfo->username) || empty($userInfo->password)) {
                continue;
            }
            $this->cookie = $this->getCookieByLogin($userInfo->username, $userInfo->password);
            if ($this->validCookie()) {
                DB::table('geekbang_provider')->where(['id'=>$item->id])->update(['status' => 1, 'cookie' => $this->cookie]);
            } else {
                $this->setProviderStatus($item->id, $item->status-1);
            }
        }
    }

    /**
     * 获取provider list
     */
    public function getProviderList()
    {
        $data = DB::table('geekbang_provider')->where(['status' => 1])->get()->toArray();
        return $data;
    }

    /**
     * 验证cookie是否过期
     * @return bool
     */
    public function validCookie()
    {
        $url = 'https://account.geekbang.org/serv/v1/user/auth';
        $data = Curl::post($url, array(), $this->getHttpHeader());
        if (!empty($data) && !empty($data['data']) && empty($data['error'])) {
            return true;
        }
        return false;
    }

    /**
     * 登陆获取cookie
     * @param $phone
     * @param $password
     * @return string
     */
    public function getCookieByLogin($phone, $password)
    {
        $url = 'https://account.geekbang.org/account/ticket/login';
        $postData = [
            "appid" => 1,
            "platform" => 3,
            "cellphone" => $phone,
            "password" => $password,
            "country" => 86,
            "captcha" => ''
        ];
        $data = Curl::postGetResponseHeader($url, json_encode($postData), $this->baseHttpHeader());
        $cookie = array();
        $headerName = 'Set-Cookie:';
        foreach ($data['head'] as $item) {
            if (strstr($item, $headerName)) {
                $i = explode(';', $item);
                $cookie[] = trim(substr(current($i), strlen($headerName)));
            }
        }
        $completeCookie = implode('; ', $cookie);
        return $completeCookie;
    }

    public function setProviderStatus($id, $status)
    {
        return DB::table('geekbang_provider')->where(['id'=>$id])->update(['status' => $status]);
    }

    public function insertCookie($cookie)
    {
        return DB::table('geekbang_provider')->insert(array(
            'provider' => '',
            'cookie' => $cookie,
            'status' => 1
        ));
    }

    private function getHttpHeader()
    {
        return array_merge($this->baseHttpHeader(), [
            'Cookie: ' . $this->cookie
        ]);
    }

    private function baseHttpHeader()
    {
        return [
            'Origin: https://account.geekbang.org',
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
        ];
    }

    private function setArticleCache($data)
    {
        Redis::set(self::ARTICLE_TOKEN.$this->article_token,json_encode($data));
    }

    private function getArticleCache()
    {
        $data = Redis::get(self::ARTICLE_TOKEN.$this->article_token);
        return empty($data) ? array() : json_decode($data, false);
    }

    private function delArticleCache()
    {
        Redis::del(self::ARTICLE_TOKEN.$this->article_token);
    }
}