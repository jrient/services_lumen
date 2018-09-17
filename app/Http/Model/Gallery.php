<?php
/**
 * Created by PhpStorm.
 * User: 32-
 * Date: 2018/9/18
 * Time: 0:09
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class Gallery
{
    public function insert($imgUrl, $datetime, $fromOpenid)
    {
        return DB::table('gallery')->insert(array(
            'datetime' => $datetime,
            'img_url' => $imgUrl,
            'from_openid' => $fromOpenid
        ));
    }
}