<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15 0015
 * Time: 10:08
 */

namespace App\Libs;

/**
 * 使用此trait的类可以支持json返回
 * Trait JSON_Return
 * @package App\Lib
 */
trait JSON_Return
{


    /*** json格式返回
     * @param string $message
     * @param int $code
     * @param array $data
     * @param string $url
     * @return \Illuminate\Http\JsonResponse
     * User: Qiyifan
     * Date: 2021/3/11 0011 下午 3:32
     */
    protected function json_response($message = 'success', $code = 200, $data = [], $url = '')
    {
        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => $data,
            'url'  => $url,
        ]);
    }
}
