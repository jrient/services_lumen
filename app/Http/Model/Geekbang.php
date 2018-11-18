<?php
/**
 * User: jrient
 * Date: 2018/11/18
 * Time: 15:13
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Geekbang
{
    public $cookie;

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
        if (!empty($bookInfo) && ($bookData['sub_time'] === $bookInfo->sub_time)) {
            return true;
        }
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
        if (empty($articleInfo)) {
            $this->saveArticle($articleData);
        } else {
            DB::table('geekbang_article')->where(['id' => $articleInfo->id])->update($articleData);
        }
        return true;
    }

    public function getBookList()
    {
        return DB::table('geekbang_book')->get()->toArray();
    }

    public function getArticleList()
    {
        return DB::table('geekbang_article')->get(['book_id', 'article_id','article_title', 'article_summary'])->toArray();
    }

    /**
     * 从本地数据库获取文章信息
     * @param $articleId
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getArticleInfo($articleId)
    {
        return DB::table('geekbang_article')->where(['article_id' => $articleId])->first();
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
        return [
            'Origin: https://account.geekbang.org',
            'Content-Type: application/json',
            'Cookie: ' . $this->cookie
        ];
    }
}