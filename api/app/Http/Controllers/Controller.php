<?php

namespace App\Http\Controllers;

use App\Definition\ReturnCode;
use App\Libs\JSON_Return;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, JSON_Return;


    /**
     * Api格式返回数据
     * @param        $code
     * @param string $message
     * @param array $data
     * @param bool $isDie
     */
    protected function responseApi(
        $code,
        string $message = '',
        array $data = [],
        bool $isDie = false
    ) {
        echo json_encode(
            [
                'code' => $code,
                'msg'  => $message,
                'data' => $data
            ]
        );
        if ($isDie) {
            die();
        }
    }

    /**
     * 空或否值返回信息
     * @param        $nil
     * @param string $msg
     * @param int $code
     */
    protected function emptyRs(
        $nil,
        string $msg = '参数错误',
        int $code = ReturnCode::ERROR_PARAMS
    ) {
        if (!$nil) {
            $this->responseApi($code, $msg, [], true);
        }
    }

    /**
     * 对多结果进行错误归类
     * @param $rs
     * @return array
     */
    protected function fmtRs($rs): array
    {
        if (count($rs) === 1) {
            $rs = end($rs);
            return [$rs['ret'], $rs['msg']];
        }
        return [max(array_column($rs, 'ret')), implode(",", array_column($rs, 'msg'))];
    }


    /**
     * 校验参数
     * @param $flag
     * @param $msg
     */
    protected function validation($flag, $msg)
    {

        if (!$flag) {
            return;
        }
        $this->responseApi(ReturnCode::ERROR_PARAMS, $msg, [], true);
    }
}
