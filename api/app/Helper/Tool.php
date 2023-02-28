<?php

namespace App\Helper;

use App\Services\Common\PublicService;
use App\Services\CompanyService;
use App\Services\Facebook\DraftService;
use App\Services\User\UserService;
use DateTime;
use Illuminate\Support\Facades\App;

/**
 * 助手工具类
 * Class Tool
 * @package App\Helper
 */
class Tool
{
    // 生成n位随机数字
    public static function createRandNum($n)
    {
        $str = '';
        for ($i = 0; $i < $n; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }

    // 检测是否为正规手机号
    public static function checkPhone($mobile)
    {
        return preg_match('/^1[34578]\d{9}$/', $mobile) ? true : false;
    }

    // 批量替换数组字符
    public static function strReplaceArr($string, $params)
    {
        return str_replace(array_keys($params), array_values($params), $string);
    }

    // 获取客户端ip
    public static function getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }
        return $ip;
    }

    // 随机字符串
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 自定义字符分割字符串
     * @param string|null $str
     * @param bool $unique
     * @param string $comma
     * @return array
     */
    public static function getArrayByComma(?string $str = '', bool $unique = true, string $comma = ','): array
    {
        if (!$str) {
            return [];
        }

        $rs = explode($comma, $str);
        $rs = array_filter(array_map(function ($v) {
            return trim($v);
        }, $rs));

        if ($unique) {
            $rs = array_unique($rs);
        }

        return array_values($rs);
    }

    /**
     * 获取一个数组的某个key
     * @param        $arr
     * @param        $key
     * @param mixed $default
     * @param bool $trim
     * @return mixed
     */
    public static function get($arr, $key, $default = '', bool $trim = true)
    {
        if (empty($arr)) {
            return $default;
        }
        return isset($arr[$key]) ? ($trim && is_string($arr[$key]) ? trim($arr[$key]) : $arr[$key]) : $default;
    }

    // 格式化curl的参数
    public static function fmtArr2StrInParams($params, $json = false)
    {
        if (empty($params)) {
            return [];
        }
        foreach ($params as $index => $item) {
            if (is_array($item)) {
                $params[$index] = json_encode($item);
            }
        }

        return $json ? json_encode($params) : $params;
    }

    // 格式化curl的参数
    public static function fmtJson2Arr($params, $falseDefault = '{}')
    {
        if (empty($params)) {
            return [];
        }
        foreach ($params as $index => $item) {
            if (empty($item) || !self::isJson($item)) {
                $params[$index] = $item ?: $falseDefault;
                continue;
            }
            $params[$index] = json_decode($item, true);
        }

        return $params;
    }

    public static function fmtRuleName($value): string
    {
        $str = "/";
        if (is_array($value)) {
            $str .= "(?=.*" . implode("|", $value) . ")";
        } else {
            $str .= "(?=.*{$value})";
        }

        $str .= "/i";
        return $str;
    }

    /**
     * 数组唯一值
     * @param $arr
     * @param string $field
     * @param bool $fmt
     * @return array
     */
    public static function getUniqueArr($arr, string $field = '', bool $fmt = false): array
    {
        if (empty($arr)) {
            return [];
        }
        if ($field) {
            $arr = array_column($arr, $field);
        }
        if ($fmt) {
            $arr = array_values(array_unique($arr));
        }
        return $arr;
    }

    /**
     * 根据广告账户名获取系统
     *
     * @param string $accountName
     * @return int
     */
    public static function getPlatformFromName(string $accountName): int
    {
        $systemInfos = CompanyService::systemInfo();
        foreach ($systemInfos as $systemInfo) {
            if (empty($systemInfo['fb_pre'])) {
                continue;
            }
            $fbPre = json_decode($systemInfo['fb_pre'], true);

            foreach ($fbPre as $pre) {
                if (strpos($accountName, $pre) === 0) {
                    return $systemInfo['platform'];
                }
            }
        }
        return -1;
    }

    /**
     * 根据商店链接返回对应的平台
     * @param $url
     * @return string
     */
    public static function getOsByUrl($url): string
    {
        if (empty($url)) {
            return 'google';
        }
        if (preg_match(self::fmtRuleName('google'), $url)) {
            return 'google';
        } else {
            return 'apple';
        }
    }

    /**
     * 判断是否是代投的广告账户
     * @param $name
     * @return int
     */
    public static function isAgentByName($name): int
    {
        return strpos($name, '代投') ? 1 : 0;
    }

    public static function fmtAid($aid)
    {
        return preg_match(self::fmtRuleName(['act_']), $aid) ? $aid : 'act_' . $aid;
    }

    // 格式化请求参数
    public static function fmtRequest($str)
    {
        return $str ? (is_string($str) ? json_decode($str, true) : $str) : [];
    }

    public static function fmtIdPreInCache($prefix)
    {
        if (is_array($prefix)) {
            $prefix = array_map(function ($v) {
                if (is_array($v)) {
                    return implode("#", $v);
                }
                return $v;
            }, $prefix);
            $prefix = implode("-", $prefix);
        }
        return $prefix;
    }

    public static function fmtIdInCache($ids, $prefix)
    {
        if (empty($ids) || empty($prefix)) {
            return $ids;
        }
        if (is_array($ids)) {
            $fmtIds = [];
            foreach ($ids as $id) {
                $fmtIds[] = md5($prefix . "_" . $id);
            }
        } else {
            $fmtIds = md5($prefix . "_" . $ids);
        }
        // 统一key值
        return $fmtIds;
    }

    // 处理multi结果
    public static function fmtMultiData($info): array
    {
        $rs = [];
        if (empty($info)) {
            return $rs;
        }
        // 数据处理 分两种情况ids请求和非ids请求
        // 过滤不需要的值（使用$ADS_CURL_ADMIN_TOKEN会产生） 如{'__fb_trace_id__':"GLL0kjI+hCk"}
        foreach ($info as $key => $data) {
            // 正常非ids请求 如 APL/<id>?.....
            if (self::get($data, 'data')) {
                $rs[] = $data;
            } elseif (is_numeric($key) && is_array($data)) {
                // ids请求 如 APL/?ids=<ids>&.....
                $rs += $data;
            }
        }
        return $rs;
    }

    // 格式化数据结果
    public static function fmtInsight($value, $times = 1, $key = '')
    {
        if ($key == 'name') {
            return $value;
        }
        if (empty($value)) {
            return 0;
        }
        if (!is_numeric($value)) {
            return $value;
        }
        $sign = $times == 100 ? '%' : '';
        if (empty($sign) && Tool::isInclude($key, '_roi')) {
            $sign = '%';
        }
        // 无小数的情况
        if (ceil($value) == $value) {
            return number_format($value) . $sign;
        }
        return number_format($value, 2) . $sign;
    }

    /**
     * 格式化时间 将2021-04-02 => 20210402
     * @param $date
     * @param string $search
     * @return array|string|string[]
     */
    public static function fmtDateString($date, $search = "-")
    {
        return str_replace($search, '', $date);
    }

    // 判断数据是否存在某个键值 key - item 相同
    public static function isExistKey($arr, $keyArrMap, &$info)
    {
        if (empty($keyArrMap)) {
            return;
        }
        foreach ($keyArrMap as $item) {
            if (isset($arr[$item])) {
                $info[$item] = $arr[$item];
            }
        }
    }

    /**
     * 生成同步请求的uri
     * @param       $path
     * @param array $param
     * @param array $field
     * @return string
     */
    public static function createBatchUrl($path, array $param = [], array $field = []): string
    {
        if ($field) {
            $param['fields'] = implode(',', $field);
        }

        if ($param) {
            $uri = http_build_query($param);
            return "$path?$uri";
        }

        return $path;
    }

    public static function decodeUrlQuery($url): array
    {
        $query = parse_url($url);
        $query_str = str_replace('?code=', '', $query['query'] ?? '');
        $query_pairs = explode('&', $query_str);
        $params = [];
        foreach ($query_pairs as $query_pair) {
            $item = explode('=', $query_pair);
            $params[$item[0]] = $item[1] ?? '';
        }
        return $params;
    }


    public static function transferFmtType($str, $fmtType)
    {
        switch ($fmtType) {
            case 'string':
                $str = (string)$str;
                break;
            case 'int':
                $str = (int)$str;
                break;
            default:
                break;
        }
        return $str;
    }

    // 字符串和整形互转 数据库的隐式转化
    public static function transferDataType($data, $fmtType = 'string')
    {
        if (is_array($data)) {
            foreach ($data as &$one) {
                $one = self::transferFmtType($one, $fmtType);
            }
        } else {
            $data = self::transferFmtType($data, $fmtType);
        }
        return $data;
    }

    // 判断类型是否是AAA
    public static function isAAA($params): bool
    {
        return self::get($params, 'smart_promotion_type') == 'SMART_APP_PROMOTION';
    }

    // 判断类型是否是商店目录
    public static function isShopMenu($params): bool
    {
        return self::get($params, 'promoted_object') && self::get($params['promoted_object'], 'product_catalog_id');
    }

    // 判断类型是否是像素
    public static function isPixel($params): bool
    {
        return self::get($params, 'pixel_id');
    }

    /**
     * 判断用户是否是管理员
     * @param $user
     * @return bool
     */
    public static function isSuper($user): bool
    {
        return in_array($user['power'], UserService::ADMIN_SHOW);
    }

    public static function fmtDomainUrl($url, $isS3): string
    {
        return ($isS3 ? config('aws_s3.domain') : config('qiniu.domain')) . self::parseUrl($url);
    }

    public static function fmtUrl($url): array
    {
        $parseUrl = $url = self::parseUrl($url);
        // 1、去掉?后面的数据 获取?的位置
        $index = strpos($url, '?');
        if ($index) {
            $url = substr($url, 0, $index);
        }
        $cdnUrl = $url;
        // 2、获取最后一个/的位置
        $end = strrpos($url, '/');
        if ($end) {
            $url = substr($url, $end + 1);
        }
        return [pathinfo($url, PATHINFO_EXTENSION), $url, $cdnUrl, $parseUrl];
    }

    /**
     *  投放系统添加标识
     * @param        $name
     * @param string $part
     * @return string
     */
    public static function fmtPublishName($name, string $part = '-kwbk'): string
    {
        return preg_match(self::fmtRuleName([$part]), $name) ? $name : $name . $part;
    }

    /**
     * 对多结果进行错误归类
     * @param $rs
     * @return array
     */
    public static function fmtRs($rs): array
    {
        if (count($rs) === 1) {
            $rs = end($rs);
            return [$rs['ret'], $rs['msg']];
        }
        return [max(array_column($rs, 'ret')), implode(",", array_column($rs, 'msg'))];
    }

    // 格式化时间 默认ISO8601
    public static function fmtTimeStamp($timeStamp, $type = 'c'): string
    {
        $dateTime = new DateTime();
        return $dateTime->setTimestamp($timeStamp)->format($type);
    }

    /**
     * 去掉url中的空格和'+'
     * @param $url
     * @return array|string|string[]
     */
    public static function parseUrl($url)
    {
        $str = str_replace(' ', '%20', $url);
        return str_replace('+', '%2B', $str);
    }

    // 通过广告账户的不同时区获取当前时间所对应的日期
    public static function getTodayDateWithTimeZone($offset, $curTimeZoneOffset = 8, $format = 'Y-m-d', $curTime = 0)
    {
        $curTime = $curTime ?: time();
        return date($format, $curTime - ($curTimeZoneOffset - $offset) * 3600);
    }

    public static function getMinuteRange($time = ''): string
    {
        $time = $time ?: time();
        $minute = intval(date("i", $time) / 10);
        return str_pad($minute, 2, 0);
    }

    /**
     * 格式化code
     * @param $codes
     * @return array
     */
    public static function fmtUserCode($codes): array
    {
        if (empty($codes)) {
            return [];
        }
        $temp = [];
        foreach ($codes as $code) {
            if (empty($code)) {
                continue;
            }
            $temp[] = strtolower($code);
        }
        return $temp;
    }

    public static function compareTwoValue($value1, $value2, $item = ''): string
    {
        // $color1 = '';
        $color2 = '';
        if (strpos($item, '_roi') !== false) {
            if ($value2 < 20) {
                $color2 = 'color:red';
            }
            // if ($value1 < 20) {
            //     $color1 = 'color:red';
            // }
        }

        return "<span><span style='" . $color2 . "'>" . Tool::fmtInsight($value2, 1, $item) . "</span>" .
            // "(<span style='" . $color1 . "'>" . Tool::fmtInsight($value1, 1, $item) . "</span>)</span>" .
            Tool::compareTwoPart(
                $value1 ?? 0,
                $value2,
                $item
            );
    }

    // 比较两个数的差异
    public static function compareTwoPart($partA, $partB, $item = ''): string
    {
        if ($partB == $partA) {
            return '<span> 持平 </span>';
        }

        $sign = "↑";
        $color = 'green';
        if ($item == 'rank') {
            if ($partB > $partA) {
                $sign = '↓';
                $color = 'red';
            }
            $msg = $sign . abs($partA - $partB);
        } else {
            $partA = str_replace("%", '', $partA);
            $partB = str_replace("%", '', $partB);
            if (!is_numeric($partA) || !is_numeric($partB)) {
                return '';
            }

            if ($partB < $partA) {
                $sign = '↓';
                $color = 'red';
            }
            if ($partA == 0 || $partB == 0) {
                $msg = $sign . '100%';
            } else {
                $msg = $sign . round(abs($partB - $partA) * 100 / $partA, 2) . "%";
            }
        }
        return '<span style=\'color: ' . $color . '\'> ' . $msg . '</span>';
    }

    /**
     * 数组去重
     * @param array $data1
     * @param array $data2
     * @return array
     */
    public static function getUnique(array $data1, array $data2 = []): array
    {
        if ($data2) {
            $data1 = array_merge($data1, $data2);
        }

        return array_values(array_unique($data1));
    }

    public static function fmtCoIdKey($name): string
    {
        return $name . "_" . PublicService::getBusinessId();
    }


    /**
     * @param $data
     * @param $keys
     * @param false $isExist 1表示存在即可 默认去除非0数据
     * @return array
     */
    public static function mGet($data, $keys, bool $isExist = false)
    {
        if (empty($data) || empty($keys)) {
            return $data;
        }
        $temp = [];
        foreach ($keys as $key) {
            if ($isExist) {
                if (isset($data[$key])) {
                    $temp[$key] = $data[$key];
                }
            } else {
                if (!self::get($data, $key)) {
                    continue;
                }
                $temp[$key] = $data[$key];
            }
        }

        return $temp;
    }

    // 判断是否是草稿Id
    public static function isDraftId($id)
    {
        return preg_match(self::fmtRuleName([DraftService::DRAFT_SIGN]), $id);
    }

    // 判断是否是发布Id
    public static function isPublishId($id)
    {
        return preg_match(self::fmtRuleName([DraftService::DRAFT_SIGN, DraftService::DRAFT_AAA_SIGN]), $id);
    }

    // 判断是否是草稿AAA Id
    public static function isDraftAAAId($id)
    {
        return preg_match(self::fmtRuleName([DraftService::DRAFT_AAA_SIGN]), $id);
    }

    public static function randomNum($curTime = ''): string
    {
        $curTime = $curTime ?: time();
        return $curTime . mt_rand(1000000, 100000000) . rand(1, 100);
    }

    public static function distinguishId($ids): array
    {
        $idArr = $ids;
        $draftIds = [];
        $fbIds = [];
        if (empty($ids)) {
            return [$fbIds, $draftIds];
        }
        foreach ($idArr as $id) {
            if (self::isPublishId($id)) {
                $draftIds[] = $id;
            } else {
                $fbIds[] = $id;
            }
        }
        return [array_values(array_unique($fbIds)), array_values(array_unique($draftIds))];
    }

    // 未满一周/一月跳过该周
    public static function fmtBetweenDate($start, $end, $dt = '', $fmt = 'Ymd'): array
    {
        if (empty($start) || empty($end)) {
            return [];
        }
        // 是否属于最后一天
        $end = Tool::checkLastDay($end, $dt);
        if ($end < $start) {
            return [];
        }
        $startTime = strtotime($start);
        $endTime = strtotime($end);


        $date = [];
        for ($curTime = $startTime; $curTime <= $endTime; $curTime += 86400) {
            $date[] = date($fmt, $curTime);
        }
        return $date;
    }

    public static function checkLastDay($date, $dt)
    {
        $timeStamp = strtotime($date);
        switch ($dt) {
            case '%Y%m':
                $lastDate = date('Ymt', $timeStamp);
                if ($date != $lastDate) {
                    $date = date("Ymd", strtotime(date('Ym01', $timeStamp)) - 86400);
                }
                break;
            case '%Y%U':
                $w = date("w", $timeStamp);
                if ($w != 6) {
                    $date = date("Ymd", $timeStamp - (($w + 1) * 86400));
                }

                break;
            default:
                break;
        }
        return $date;
    }

    // 判断是否字段是否包含
    public static function isInclude($str, $match)
    {
        return preg_match(self::fmtRuleName([$match]), $str);
    }

    public static function removeSign($str, $sign)
    {
        return str_replace($sign, '', $str);
    }

    // 获取周的开始和结束日期
    public static function fmtWeek($year, $weekNum): array
    {
        $firstDayOfYear = mktime(0, 0, 0, 1, 1, $year);
        $firstWeekDay = date('N', $firstDayOfYear);
        $firstWeekNum = date('W', $firstDayOfYear);
        if ($firstWeekNum == 1) {
            $day = (1 - ($firstWeekDay - 1)) + 7 * ($weekNum - 1);
            $startDate = date('Ymd', mktime(0, 0, 0, 1, $day, $year));
            $endDate = date('Ymd', mktime(0, 0, 0, 1, $day + 6, $year));
        } else {
            $day = (8 - $firstWeekDay) + 7 * ($weekNum - 1);
            $startDate = date('Ymd', mktime(0, 0, 0, 1, $day, $year));
            $endDate = date('Ymd', mktime(0, 0, 0, 1, $day + 6, $year));
        }
        return [$startDate, $endDate];
    }


    // 获取月的开始和结束日期
    public static function fmtMonth($date): array
    {
        $time = Tool::fmtDateToTimeStamp($date);
        return [date('Ym01', $time[0]), date('Ymt', $time[0])];
    }

    // 获取月的开始和结束日期
    public static function fmtYear($date): array
    {
        $time = Tool::fmtDateToTimeStamp($date);
        $year = date("Y", $time[0]);
        return [$year . '0101', $year . '1231'];
    }

    public static function fmtDateToTimeStamp(...$dateArr): array
    {
        $temp = [];
        foreach ($dateArr as $date) {
            $temp[] = strtotime($date);
        }
        return $temp;
    }

    public static function shortUrl($input): array
    {
        $base32 = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '0', '1', '2', '3', '4', '5'
        ];

        $hex = md5($input);
        $hexLen = strlen($hex);
        $subHexLen = $hexLen / 8;
        $output = [];

        for ($i = 0; $i < $subHexLen; $i++) {
            //把加密字符按照8位一组16进制与0x3FFFFFFF(30位1)进行位与运算
            $subHex = substr($hex, $i * 8, 8);
            $int = 0x3FFFFFFF & bin2hex($subHex);
            $out = '';

            for ($j = 0; $j < 6; $j++) {

                //把得到的值与0x0000001F进行位与运算，取得字符数组chars索引
                $val = 0x0000001F & $int;
                $out .= $base32[$val];
                $int = $int >> 5;
            }

            $output[] = $out;
        }

        return $output;
    }

    public static function exportCsv($tableName, $columns, $allData)
    {
        $list = array_merge($columns, $allData);
        ob_start();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $tableName . '"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'w');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $list);
        $allcount = count($list);//从数据库获取总量，假设是一百万
        $count = 1000;//每次查询的条数
        $pages = ceil($allcount / $count);
        for ($i = 1; $i <= $pages; $i++) {
            $output_data = array_slice($list, ($i - 1) * $count, $count);
            foreach ($output_data as &$item) {
                fputcsv($fp, $item);
                unset($item);//释放变量的内存
            }
            ob_flush();//刷新输出缓冲到浏览器
            flush();//必须同时使用ob_flush()和flush()函数来刷新输出缓冲。
        }
        fclose($fp);
    }

    /** 获取302重定向url
     * @param        $url
     * @param string $cookie
     * @return mixed|string
     */
    public static function getRedirectUrl($url, string $cookie = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1800);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt(
            $curl,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 6.1; WOW64) ' .
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36'
        );
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }

        $rs = curl_exec($curl);

        // $headers = curl_getinfo($curl);
        $errno = curl_errno($curl);
        if (empty($rs) || $errno) {
            $rs = '';
        }
        curl_close($curl);

        return $rs;
    }

    public static function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // 计算金额用户成本
    public static function calCost($dividend, $divisor, $ratio = 2)
    {
        if ($divisor == 0) {
            return 0;
        } else {
            return round($dividend / $divisor, $ratio);
        }
    }

    public static function calRatioNew($dividend, $divisor)
    {
        if ($divisor == 0) {
            return 0;
        } else {
            return round(($dividend / $divisor) * 100, 2);
        }
    }

    public static function buildDayIndex($date): array
    {
        $date = date("Ymd", strtotime($date));
        $temp = [];
        for ($i = 0; $i < 24; $i++) {
            $temp[] = $date . str_pad($i, 2, 0, STR_PAD_LEFT);
        }
        return $temp;
    }

    // 删除文件夹
    public static function delDir($dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullPath = $dir . "/" . $file;
                if (!is_dir($fullPath)) {
                    unlink($fullPath);
                } else {
                    self::delDir($fullPath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        return rmdir($dir);
    }

    /**
     * 判断数组是否为索引（二维）数组
     * @param array $array
     * @return bool
     */
    public static function isIndexArray(array $array): bool
    {
        if (is_array($array)) {
            $keys = array_keys($array);
            return $keys === array_keys($keys);
        }

        return false;
    }

    /** 获取13位毫秒数
     * @return float
     */
    public static function getMillisecond(): float
    {
        $time = explode(" ", microtime());
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode(".", $time);
        $time = $time2 [0];
        return $time;
    }

    /**
     * 格式化Etc/GMT 时区
     * @param $date
     * @return array
     */
    public static function fmtEtcTimezone($date): array
    {
        // Etc/GMT+7
        $rs = [false, 0];
        if (!self::isInclude($date, 'Etc\/GMT')) {
            return $rs;
        }
        $timezone = substr($date, 7);
        return [true, -($timezone)];
    }

    public static function replace_unicode_escape_sequence($match)
    {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }

    // unicode to string
    public static function unicodeToString($msg)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'self::replace_unicode_escape_sequence', $msg);
    }

    public static function isInConsole(): bool
    {
        return (int)App::runningInConsole();
    }

    public static function clipboard($e = 26): string
    {
        $randomString = "";
        $str = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz123456789-";
        $strLen = strlen($str);
        for ($i = 0; $i < $e; $i++) {
            $randomString .= $str[(time() + mt_rand(0, 10000)) % $strLen];
        }
        return 'a3c' . $randomString . 'd2s';
    }

    public static function fmtHMFromSec($sec): string
    {
        if ($sec == 0) {
            return '00:00';
        }
        $m = intval($sec / 3600);
        $s = intval(($sec % 3600) / 60);
        return str_pad($m, 2, 0, STR_PAD_LEFT) . ':' . str_pad($s, 2, 0, STR_PAD_LEFT);
    }

    public static function excel($file)
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
            $data = $reader->getSheet(0)->toArray();
        });
        unlink($filePath);
        return $data;
    }

    public static function f2Sec($str)
    {
        $h = explode(':', $str);
        return $h[0] * 3600 + $h[1] * 60;
    }
}
