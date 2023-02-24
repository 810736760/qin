<?php

namespace App\Libs;

use App\Helper\Tool;

/**
 * 批量请求
 * Class MultiRequest
 * @package App\Api\Lib
 */
class MultiRequest
{
    /**
     * @param array $params
     * @param bool $mergeResponse 是否合并返回结果
     * @return array
     */
    public static function multiFetch(array $params, bool $mergeResponse = true): array
    {
        if (empty($params)) {
            return [];
        }
        $mh = curl_multi_init(); // 初始化一个curl_multi句柄
        $handles = [];
        foreach ($params as $key => $param) {
            $ch = curl_init(); // 初始化一个curl句柄
            $url = $param["url"];
            $data = $param["params"];
            $method = $param['method'];
            // 根据method参数判断是post还是get方式提交数据
            if (strtolower($method) === "get") {
                $url = "$url?" . http_build_query($data); // get方式
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // post方式
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

            curl_multi_add_handle($mh, $ch);
            $handles[(int)$ch] = $key;
        }
        $running = null;
        $curls = []; // curl数组用来记录各个curl句柄的返回值
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
            while (($ret = curl_multi_info_read($mh)) !== false) {
                // 循环读取curl返回，并根据其句柄对应的key一起记录到$curls数组中,保证返回的数据不乱序
                $curls[$handles[(int)$ret["handle"]]] = $ret;
            }
        } while ($running > 0);

        $response = [];
        foreach ($curls as $key => $val) {
            $response[$key] = curl_multi_getcontent($val["handle"]);
            curl_multi_remove_handle($mh, $val["handle"]); // 移除curl句柄
        }
        curl_multi_close($mh); // 关闭curl_multi句柄
        ksort($curls);
        // 返回数据汇总
        $info = [];
        foreach ($response as $key => $data) {
            $data = json_decode($data, true);

            if (Tool::get($data, 'error')) {
                \Log::info("curl请求失败=>({$data['error']['code']}):" . $data['error']['message'], $params[$key]);
            }

            if ($mergeResponse) {
                $info += $data; // 保留KEY值
            } else {
                $info[] = $data;
            }
        }

        return $info;
    }
}
