<?php

use Illuminate\Support\Facades\App;

/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 2017/5/13
 * Time: 下午5:07
 *
 * curl方式访问url
 * @param string $url 访问url
 * @param int $flbg 返回结果是否通过json_decode转换成数组 0 转换 1 不转换
 * @param int $type 访问方式 0 get 1 post
 * @param array $post_data post访问时传递的数据
 * @param array $headers 访问时需要传递的header参数
 * @return mixed
 */
function requestUrl($url, $flbg = 0, $type = 0, $post_data = array(), $headers = array())
{
    // 初始化一个 cURL 对象
    $curl = curl_init();
    // 设置你需要抓取的URL
    curl_setopt($curl, CURLOPT_URL, $url);
    // 设置header
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    if ($type == 1) {       // post请求
        curl_setopt($curl, CURLOPT_POST, 1);
        $post_data = is_array($post_data) ? http_build_query($post_data) : $post_data;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
    if (curl_errno($curl)) {
        \Illuminate\Support\Facades\Log::info('Curl error: ' . curl_error($curl));
    }

    // 运行cURL，请求网页
    $data = curl_exec($curl);
    // 关闭URL请求
    curl_close($curl);

    if (!$flbg) {
        $data = json_decode($data, true);
    }
    return $data;
}

function curlPostSsl($url, $vars, $mchid, $second = 30, $aHeader = array())
{
    $ch = curl_init();
    //超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    //以下两种方式需选择一种

    //第一种方法，cert 与 key 分别属于两个.pem文件
    //默认格式为PEM，可以注释
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
    curl_setopt($ch, CURLOPT_SSLCERT, config_path() . '/cert/' . $mchid . '_cert.pem');
    //默认格式为PEM，可以注释
    curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
    curl_setopt($ch, CURLOPT_SSLKEY, config_path() . '/cert/' . $mchid . '_key.pem');

    //第二种方式，两个文件合成一个.pem文件
//        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

    if (count($aHeader) >= 1) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
    }

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    $data = curl_exec($ch);
    if ($data) {
        curl_close($ch);
        return $data;
    } else {
        $error = curl_errno($ch);
        echo "call faild, errorCode:$error\n";
        curl_close($ch);
        return false;
    }
}


function generatePassword($pw_length = 6)
{
    $randPwd = "";
    for ($i = 0; $i < $pw_length; $i++) {
        $up = rand(0, 1);
        $str = chr(mt_rand(97, 122));
        $randPwd .= $up ? strtoupper($str) : $str;
    }
    return $randPwd;
}


/**
 * 获取IP
 * @User yaokai
 * @return string
 */
function getIP()
{
    /* 客户端IP */
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    } else {
        $onlineip = '127.0.0.1';
    }
    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $ip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';

    return $ip;
}


/**
 * 生成订单号
 * @User yaokai
 * @param int $length
 * @param string $tab
 * @return string
 */
function getOrderNum($length = 6, $tab = '')
{
    $dt = date('YmdHis');

    $str = $dt . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $length);

    return $str . $tab;
}

//随机数
if (!function_exists('getNonceStr')) {
    function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

/**
 * 输出xml字符
 **/
function toXml($data)
{
    $xml = "<xml>";
    foreach ($data as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}

/**
 * 将xml转为array
 */
function fromXml($xml)
{
    if (!$xml) {
        return false;
    }
    //将XML转为array
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $t = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $t;
}


function calRatio($dividend, $divisor)
{
    if ($divisor == 0) {
        return '0%';
    } else {
        return round($dividend / $divisor, 4) * 100 . '%';
    }
}

// 计算金额用户成本
function calCost($dividend, $divisor, $ratio = 2)
{
    if ($divisor == 0) {
        return 0;
    } else {
        return round($dividend / $divisor, $ratio);
    }
}

function calRatioNew($dividend, $divisor)
{
    if ($divisor == 0) {
        return 0;
    } else {
        return round(($dividend / $divisor) * 100, 2);
    }
}


/**
 * 二维数组根据某个字段排序
 * @param array $array 要排序的数组
 * @param string $keys 要排序的键字段
 * @param string $sort 排序类型  SORT_ASC     SORT_DESC
 * @return array 排序后的数组
 */
function arraySort($array, $keys, $sort = SORT_DESC)
{
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}


/**
 * 整理小说格式
 * @param string $content 内容
 * @param bool $is_first 是否需要在首尾加p标签
 * @return mixed|string
 */
function replaceFormatContent($content, $is_first = true)
{
    $rs = trim($content);
    $rs = str_replace([ "&nbsp;", "\r" ], '', $rs);
    //替换标题
    $rs = str_replace('#', '<p class="title">', $rs);
    $rs = str_replace('%', '</p>', $rs);


    // 替换换行符
    $rs = preg_replace("/(\n)+/", '</p><p>', $rs);
    $rs = str_replace([ "<br/>", "<br />", "<br>", "</br>" ], '</p><p>', $rs);
    if ($is_first) {
        $rs = '<p>' . $rs . '</p>';
    }

    // 清楚段首空格
    $rs = preg_replace("/<p>( |　)+/", '<p>', $rs);
    $rs = str_replace("<p></p>", '', $rs);

    $rs = str_replace([ "<p><p>", "</p></p>" ], [ "<p>", "</p>" ], $rs);
    $rs = str_replace('\n', '', $rs);

    //去除<p class="title">前多余的P标签
    $rs = str_replace('<p><p class="title">', '<p class="title">', $rs);
    return $rs;
}


/**
 * 导出excel
 * @param string $name 导出文件名
 * @param array $data 导出数组数据
 */
function excelExport($name, $data)
{
    $excel = \App::make('excel');
    $excel->create($name, function ($excel) use ($data) {
        $excel->sheet('score', function ($sheet) use ($data) {
            $sheet->rows($data);
        });
    })->export('xlsx');
}

/**
 * 获取今日/昨日/本周/上周/本月/上月/本季度/上季度/今年/上年的开始时间戳和结束时间戳
 * @return mixed
 */
function makeTime()
{
    //今日的开始时间戳和结束时间戳
    $today_begin = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    $today_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
    $times['today']['begin'] = $today_begin;
    $times['today']['end'] = $today_end;

    //昨日的开始时间戳和结束时间戳
    $yesterday_begin = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
    $yesterday_end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
    $times['yesterday']['begin'] = $yesterday_begin;
    $times['yesterday']['end'] = $yesterday_end;

    //本周开始时间戳和结束时间戳
    $thisweek_begin = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y'));
    $thisweek_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y'));
    $times['this_week']['begin'] = $thisweek_begin;
    $times['this_week']['end'] = $thisweek_end;

    //上周的开始时间戳和结束时间戳
    $lastweek_begin = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
    $lastweek_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
    $times['last_week']['begin'] = $lastweek_begin;
    $times['last_week']['end'] = $lastweek_end;

    //本月的开始时间戳和结束时间戳
    $thismonth_begin = mktime(0, 0, 0, date('m'), 1, date('Y'));
    $thismonth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
    $times['this_month']['begin'] = $thismonth_begin;
    $times['this_month']['end'] = $thismonth_end;

    //上个月的开始时间戳和结束时间戳
    $lastmonth_begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
    $lastmonth_end = mktime(23, 59, 59, date('m'), 0, date('Y'));
    $times['last_month']['begin'] = $lastmonth_begin;
    $times['last_month']['end'] = $lastmonth_end;

    //本季度的开始时间戳和结束时间戳
    $season = ceil((date('n')) / 3);//当月是第几季度
    $thisseason_begin = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
    $thisseason_end = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date('Y'))), date('Y'));
    $times['this_season']['begin'] = $thisseason_begin;
    $times['this_season']['end'] = $thisseason_end;

    //上季度的开始时间戳和结束时间戳
    $season = ceil((date('n')) / 3) - 1;//上季度是第几季度
    $lastseason_begin = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
    $lastseason_end = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date('Y'))), date('Y'));
    $times['last_season']['begin'] = $lastseason_begin;
    $times['last_season']['end'] = $lastseason_end;

    //今年的开始时间戳和结束时间戳
    $thisyear_begin = mktime(0, 0, 0, 1, 1, date('Y'));
    $thisyear_end = mktime(23, 59, 59, 12, 31, date('Y'));
    $times['this_year']['begin'] = $thisyear_begin;
    $times['this_year']['end'] = $thisyear_end;

    //上年的开始时间戳和结束时间戳
    $lastyear_begin = mktime(0, 0, 0, 1, 1, date('Y') - 1);
    $lastyear_end = mktime(23, 59, 59, 12, 31, date('Y') - 1);
    $times['last_year']['begin'] = $lastyear_begin;
    $times['last_year']['end'] = $lastyear_end;

    return $times;
}

/**
 * 输入开始结束时间，获取该区间的时间列表
 * @param $startdate 开始时间
 * @param $enddate 结束时间
 * @param string $format 格式化的格式
 * @return array
 */
function getDateRange($startdate, $enddate, $format='Y-m-d')
{
    $stime = strtotime($startdate);
    $etime = strtotime($enddate);
    $datearr = [];
    while ($stime <= $etime) {
        $datearr[] = date($format, $stime);//得到data arr的日期数组。
        $stime = $stime + 86400;
    }
    return $datearr;
}

/**
 * 阅读币金额处理
 * @param $money
 */
function expenseRound($money)
{
    return round($money / 130, 2);
}


/**
 * 返回以col列为主键的数组
 * @param $array
 * @param $col
 */
function pluckArray($array, $col)
{
    $data = [];
    foreach ($array as $v) {
        $data[$v[$col]] = $v;
        unset($data[$v[$col]][$col]);
    }
    return $data;
}

/**
 * 获取系统毫秒级时间戳
 * @return float
 */
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}


/**
 * 判断文件类型(不符合规则的文件不能上传)
 * @param $file
 * @return bool
 * @throws \GuzzleHttp\Exception\GuzzleException
 */
function judgeFileType($file)
{
    // 判断参数类型
    $is_file = is_file($file);
    // fileresource|filepath
    if ($is_file) {
        $type = gettype($file) == 'object' ? 0 : 1;
    } else {    // file_url
        $type = 2;
    }
    // 获取文件的mimes类型
    switch ($type) {
        case 0:
            $file_type = $file->getMimeType();
            break;
        case 1:
            $file_type = mime_content_type($file);
            break;
        case 2:
            $Client = new \GuzzleHttp\Client();
            $res = $Client->request('GET', $file);
            $content_type = $res->getHeader('content-type');
            $file_type = $content_type[0];
            break;
    }

    $mimes_type = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
        'audio/mpeg',
        'application/pdf',
        'image/jpeg;charset=UTF-8'
    ];
    return in_array($file_type, $mimes_type) ? true : false;
}

function filterAfterSign($string, $sign)
{
    $location = strpos($string, $sign);

    if ($location !== false) {
        return substr($string, 0, $location);
    }

    return $string;
}


/**
 * 简单的 int 转中文
 * @User yaokai
 * @param $num
 * @return string
 */
function toChineseNum($num)
{
    $char = [ "零", "一", "二", "三", "四", "五", "六", "七", "八", "九" ];
    $dw = [ "", "十", "百", "千", "万", "亿", "兆" ];
    $retval = "";
    $proZero = false;
    for ($i = 0; $i < strlen($num); $i++) {
        if ($i > 0) {
            $temp = (int)(($num % pow(10, $i + 1)) / pow(10, $i));
        } else {
            $temp = (int)($num % pow(10, 1));
        }

        if ($proZero == true && $temp == 0) {
            continue;
        }

        if ($temp == 0) {
            $proZero = true;
        } else {
            $proZero = false;
        }

        if ($proZero) {
            if ($retval == "") {
                continue;
            }
            $retval = $char[$temp] . $retval;
        } else {
            $retval = $char[$temp] . $dw[$i] . $retval;
        }
    }
    if ($retval == "一十") {
        $retval = "十";
    }
    return $retval;
}

/**
 * 获取指定字符串之间的字符
 * @User yaokai
 * @param $input 指定的字符串
 * @param $start 开始字符
 * @param $end 结束字符
 * @return bool|string
 */
function getBetweenStr($input, $start, $end)
{
    $substr = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
    return $substr;
}

/***导出excel
 * @param $name
 * @param $data
 * User: Qiyifan
 * Date: 2021/5/12 0012 上午 10:36
 */
function exportExcel($name, $data)
{
    $excel = App::make('excel');
    $excel->create($name, function ($excel) use ($data) {
        $excel->sheet('score', function ($sheet) use ($data) {
            $sheet->rows($data);
        });
    })->export('xlsx');
}

/**
 * 简体转繁体
 * @param $str
 * @return mixed
 */
function traditional($str)
{
    if (env('APP_ENV') == 'local') {
        $content = $str;
    } else {
        //简体转繁体
        $traditional = opencc_open("s2t.json"); //传入配置文件名
        $content = opencc_convert($str, $traditional);
        opencc_close($traditional);
    }

    return $content;
}

/**
 * 返回两个日期之间的日期数组
 * @param $start *开始时间
 * @param $end *结束时间
 * @param $type *日期格式  Y-m-d  or  Ymd
 * @return array
 */
function getDateArr($start, $end, $type)
{
    $date = [];
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    while ($dt_start <= $dt_end) {
        $date[] = date($type, $dt_start);
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $date;
}

/**
 * 随机
 * @param $num
 * @return int
 */
function getRand($num)
{
    return rand(0, $num - 1);
}

/**
 * 截取内容
 * @param $content
 * @param int $length
 * @param int $platform
 * @return mixed|string
 */
function formatContent($content, $length = 10, $platform = 0)
{
    $content = str_replace('</p>', '', $content);
    $content = str_replace([ '<br>', '<p>' ], '', $content);
    if (in_array($platform, config('platform')['HK'])) {
        $content = mb_substr($content, 0, $length);
    } else {
        $words = explode(" ", $content);
        $content = implode(" ", array_splice($words, 0, $length));
    }
    return $content;
}




/*
 * @Description: curl请求
 * @param $url
 * @param null $data
 * @param string $method
 * @param array $header
 * @param bool $https
 * @param int $timeout
 * @return mixed
 */

function curlRequest(
    $url,
    $data = null,
    $method = 'get',
    $header = [ "content-type: application/json" ],
    $https = true,
    $timeout = 5,
    $toArray = 1
) {
    $method = strtoupper($method);
    $ch = curl_init();//初始化
    curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
    }
    if ($method != "GET") {
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
        }
        if ($method == 'PUT' || strtoupper($method) == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
    //curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
    $result = curl_exec($ch);//执行请求
    curl_close($ch);//关闭curl，释放资源

    if ($toArray) {
        $result = json_decode($result, true);
    }
    return $result;
}

/**
 * 书本相关公共库
 *
 * @return void
 */
function getBookDatabase()
{
    return config('database.connections.mysql_book.database');
}

/**
 * 读取Excel
 * @param $file
 * $file=$request->file('excel');
 */
function excel($file)
{
    $fileextension = $file->getClientOriginalExtension();
    if ($fileextension != 'xlsx') {
        return false;
    }
    $newName = uniqid() . '.xlsx';
    $file->move(base_path() . '/storage/app', $newName);
    $filePath = base_path() . '/storage/app/' . $newName;
    $excel = App::make('excel');
    $data = [];
    $excel->load($filePath, function ($reader) use (&$data) {
        $data = $reader->all()->toArray();
    });
    unlink($filePath);
    return $data;
}

function getSign($data)
{
    $key = '48_bDgo0uPlxkPVWZxW4hppNYZIArCzrsqJa2PaN3KQIr6qiGKRFUYmNFUbcA89R2AZlDjsxE3idt4uPMHEk4CJkGQnGNtwg9GC-wnFT4D_FS9xAJpHff5BanRLvwvXax0smxgFCEt7MkSIzaoCLKXiAFAFER';
    #排序数组
    ksort($data);
    //去除sign参数
    unset($data['sign']);
    #处理生成签名
    $str = '';
    foreach ($data as $k => $v) {
        $str .= $k . '=' . $v . '&';
    }
    $str .= 'key=' . $key;
    $sign = strtoupper(md5($str));
    return $sign;
}

/**
 * 付费趋势标红标绿-回本
 *
 * @param $bookId 书本
 * @param $numD1 标准值-第一天
 * @param $num 标准值
 * @param $num2 实际值
 * @return string 返回给前端css样式值
 */
function payTrendReColor($numD1, $num, $num2, $bookId, $bookModel)
{
    if ($bookId) {
        $numD1 = str_replace('%', '', $numD1);
        $num = str_replace('%', '', $num);
        $num2 = str_replace('%', '', $num2);
        $d1 = ($bookModel && $bookModel->re_d1) ? $bookModel->re_d1 : 0;
        $rate = ($bookModel && $d1 && $numD1) ? $d1 / $numD1 : 1;
        $level1 = ($bookModel && $bookModel->re_level1) ? $bookModel->re_level1 : -20;
        $level2 = ($bookModel && $bookModel->re_level2) ? $bookModel->re_level2 : -10;
        $level3 = ($bookModel && $bookModel->re_level3) ? $bookModel->re_level3 : 10;
        $level4 = ($bookModel && $bookModel->re_level4) ? $bookModel->re_level4 : 20;
        $sub = $num2 - $num * $rate;
        if ($sub <= $level1) { // 极差
            return '#FF9C9C';
        } elseif ($level1 < $sub && $sub <= $level2) { // 差
            return '#FFE1E1';
        } elseif ($level2 < $sub && $sub <= $level3) { // 正常
            return '#E6F0FF';
        } elseif ($level3 < $sub && $sub <= $level4) { // 良好
            return '#C5EFD1';
        } else { // 优秀
            return '#9DDC9D';
        }
    } else {
        return '';
    }
}

/**
 * 付费趋势标红标绿-增率
 *
 * @param $bookId 书本
 * @param $numD1 标准值-第一天
 * @param $num 标准值
 * @param $num2 实际值
 * @return string 返回给前端css样式值
 */
function payTrendAddColor($numD1, $num, $num2, $bookId, $bookModel)
{
    if ($bookId) {
        $numD1 = str_replace('%', '', $numD1);
        $num = str_replace('%', '', $num);
        $num2 = str_replace('%', '', $num2);
        $d1 = ($bookModel && $bookModel->add_d1) ? $bookModel->add_d1 : 0;
        $rate = ($bookModel && $d1 && $numD1) ? $d1 / $num : 1;
        $level1 = ($bookModel && $bookModel->add_level1) ? $bookModel->add_level1 : -10;
        $level2 = ($bookModel && $bookModel->add_level2) ? $bookModel->add_level2 : -5;
        $level3 = ($bookModel && $bookModel->add_level3) ? $bookModel->add_level3 : 5;
        $level4 = ($bookModel && $bookModel->add_level4) ? $bookModel->add_level4 : 10;
        $sub = $num2 - $num * $rate;
        if ($sub <= $level1) { // 极差
            return '#FF9C9C';
        } elseif ($level1 < $sub && $sub <= $level2) { // 差
            return '#FFE1E1';
        } elseif ($level2 < $sub && $sub <= $level3) { // 正常
            return '#E6F0FF';
        } elseif ($level3 < $sub && $sub <= $level4) { // 良好
            return '#C5EFD1';
        } else { // 优秀
            return '#9DDC9D';
        }
    } else {
        return '';
    }
}
