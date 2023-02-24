<?php
/**
 * Created by PhpStorm.
 * User: Qiyifan
 * Date: 2021/8/16 0016
 * Time: 下午 1:44
 */

namespace App\Helper;

class RequestHelper
{


    /**
     * 请求表格型数据
     * @param $request
     * @return array
     */
    public static function getBaseRequest($request, $pageCountLabel = 'page_size'): array
    {
        $page = $request['page'] ?? 1;
        $pageCount = $request[$pageCountLabel] ?? 20;
        return [$page, $pageCount];
    }
}
