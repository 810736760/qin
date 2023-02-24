<?php
/**
 * Created by PhpStorm.
 * Title：公共函数
 * User: yaokai
 * Date: 2018/3/29 0029
 * Time: 11:21
 */

function dda($model)
{
    if (method_exists($model, 'toArray')) {
        dd($model->toArray());
    } else {
        dd($model);
    }
}
/**
 * ajax请求返回数据格式
 * Enter description here .
 * ..
 *
 * @param unknown_type $msg
 * @param unknown_type $code
 * @param unknown_type $forwardUrl
 */
function AjaxCallbackMessage($msg = '', $code = true, $forwardUrl = '')
{
    $array = array(
        "status" => $code,
        "message" => $msg,
        "forwardUrl" => $forwardUrl
    );

    return json_encode($array);
}


/**
 * 提示处理
 * @User yaokai
 * @param $message
 * @param string $status
 * @param null $redirect
 * @param int $timeout
 * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
 */
function prompt($message, $status = 'success', $redirect = null, $timeout = 3)
{
    if (is_string($message)) {
        $message = ['info' => $message, 'title' => $message];
    }
    $data = [
        'status' => $status,
        'message' => $message
    ];
    if ($redirect == -1) {
        $redirect = URL::previous();
        if ($status == 'error' && Request::isMethod('post') && !Request::ajax()) {
            Request::flash();
        }
    }
    if ($redirect) {
        $data['redirect'] = $redirect;
        if ($timeout > 0) {
            $data['timeout'] = $timeout;
        } elseif (!Request::ajax()) {
            return redirect($redirect);
        }
    }
    if (Request::ajax()) {
        return $data;
    } else {
        return view('prompt', $data);
    }
}




/**
 * ----------------------------------
 * update batch 批量更新
 * ----------------------------------
 * @User yaokai
 * @param string $tableName  ( required | string )
 * @param array $multipleData ( required | array of array )
 * @return bool|int
 * //test data
 *
 * $multipleData = array(
 *      array(
 *          'title' => 'My title' ,
 *          'name' => 'My Name 2' ,
 *          'date' => 'My date 2'
 *      ),
 *      array(
 *          'title' => 'Another title' ,
 *          'name' => 'Another Name 2' ,
 *          'date' => 'Another date 2'
 *      )
 *  )
 *
 * multiple update in one query
 *
 * 引用：https://stackoverflow.com/questions/26133977/laravel-bulk-update
 */
function batchUpdate($tableName = "", $multipleData = array())
{
    if ($tableName && !empty($multipleData)) {

        // column or fields to update
        $updateColumn = array_keys($multipleData[0]);
        $referenceColumn = $updateColumn[0]; //e.g id
        unset($updateColumn[0]);
        $whereIn = "";

        $q = "UPDATE ".$tableName." SET ";
        foreach ($updateColumn as $uColumn) {
            $q .=  $uColumn." = CASE ";

            foreach ($multipleData as $data) {
                $q .= "WHEN ".$referenceColumn." = ".$data[$referenceColumn]." THEN '".$data[$uColumn]."' ";
            }
            $q .= "ELSE ".$uColumn." END, ";
        }
        foreach ($multipleData as $data) {
            $whereIn .= "'".$data[$referenceColumn]."', ";
        }
        $q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";

        // Update
        return DB::update(DB::raw($q));
    } else {
        return false;
    }
}

/**
 * 数据大  拆分批量修改
 * @User yaokai
 * @param string $tableName
 * @param array $multipleData
 * @param int $size
 */
function batchChunkUpdate($tableName = "", $multipleData = array(), $size = 500)
{
    if (count($multipleData) > $size) {
        $chunk = array_chunk($multipleData, $size);
        foreach ($chunk as $item) {
            batchUpdate($tableName, $item);
        }
        return true;
    } else {
        return batchUpdate($tableName, $multipleData);
    }
}

function autopush_log($count, $type, $status = 1)
{
    //推送结束添加推送记录，取脚本描述为推送类型
    $data = [ 'push_number' => $count, 'push_type' => $type ,'status' => $status];
    \App\Models\AutoPush_Log::insert($data);
}


function qiniuRefresh($urls = [], $dirs = [])
{
    $data = json_encode(compact('urls', 'dirs'));

    $url = 'http://fusion.qiniuapi.com/v2/tune/refresh';//缓存刷新接口

    $auth = new Qiniu\Auth(config('UEditorUpload.core.qiniu.accessKey'), config('UEditorUpload.core.qiniu.secretKey'));

    $res = $auth->authorization($url, $data, 'application/json');//获取签名

    return requestUrl($url, 0, 1, $data, [
        "Authorization:".$res['Authorization'],
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
}
