<?php

namespace App\Libs;

/**
 * 单次请求
 * Class SimpleRequest
 * @package App\Libs
 */
class SimpleRequest
{

    //重试次数
    protected $maxTimes;

    //GET或者POST
    protected $originType;
    protected $type;

    //请求的参数
    protected $params = [];

    //请求的头部信息，包括cookie之类的
    protected $options = [];

    //请求的路径,包括端口，不包括参数
    protected $url;

    //curl句柄
    protected $ch;

    //请求结果
    protected $response;

    // 响应头
    protected $responseHeaders = [];

    protected $responseCookies = [];

    //请求状态
    protected $state;

    //默认超时时间,ms单位
    const DEFAULT_TOTAL_TIMEOUT = 800;

    //默认超时时间,ms单位
    const DEFAULT_CONECTION_TIMEOUT = 100;
    const DEFAULT_SSL_CONECTION_TIMEOUT = 500;

    //默认重试次数
    const DEFAULT_MAX_TIMES = 5;

    //请求成功
    const SUCCESS = 1;

    //请求失败
    const FAILER = 0;

    const HTTP_GET = 'http_get';
    const HTTP_POST = 'http_pos';

    /**
     * 请求类型
     */
    const REQUEST_TYPE_GET = 'get';
    const REQUEST_TYPE_POST = 'post'; // 适合传入一维数组
    const REQUEST_TYPE_POST_RAW_DATA = 'postdata'; // 适合传入原始报文
    const REQUEST_TYPE_TK_POST = 'tk_post'; // tiktok
    const REQUEST_TYPE_TK_GET = 'tk_get'; // tiktok

    const REQUEST_TYPE_GOOGLE_POST = 'gg_post'; // google
    //是否使用代理
    protected $useProxy = false;
    protected $proxyInfos = [

    ];

    /**
     * 构造函数
     * @param string $type 请求类型，见const REQUEST_TYPE_* | 或者自行传入 'PUT'、'DELETE'等可用的method
     * @param string $url 请求的地址，除了参数，其他都要填上
     * @param array $options 包含超时时间，重试次数，头部信息，http协议或https协议
     */
    public function __construct($type, $url, $options = [])
    {
        $this->originType = $type;
        $this->type = strtolower($type);
        $this->url = $url;
        $this->options = $options;

        $this->options['total_timeout'] =
            empty($options['total_timeout']) ? self::DEFAULT_TOTAL_TIMEOUT : $options['total_timeout'];
        $this->options['connection_timeout'] =
            empty($options['connection_timeout']) ? self::DEFAULT_CONECTION_TIMEOUT : $options['connection_timeout'];
        $this->options['return_transfer'] =
            empty($options['return_transfer']) ? true : $options['return_transfer'];

        $this->options['headers'] =
            empty($options['headers']) ? [] : $options['headers'];

        $this->maxTimes =
            empty($options['max_times']) ? self::DEFAULT_MAX_TIMES : $options['max_times'];

        if ($this->options['connection_timeout'] <= self::DEFAULT_SSL_CONECTION_TIMEOUT
            && strpos($url, 'https') === 0
        ) {
            $this->options['connection_timeout'] = self::DEFAULT_SSL_CONECTION_TIMEOUT;
        }
    }

    //初始化curl
    protected function requestInit()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($this->ch, CURLOPT_ENCODING, $this->options['encoding'] ?? 'gzip');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->options['return_transfer']);

        if (isset($this->options['max_redirs'])) {
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, $this->options['max_redirs']);
        }

        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, $this->options['http_version'] ?? CURL_HTTP_VERSION_1_0);
        if ($this->options['fetchResponseHeader'] ?? false === true) {
            curl_setopt($this->ch, CURLOPT_HEADER, true);
        }

        curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $this->options['total_timeout']);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $this->options['connection_timeout']);

        if (isset($this->options['http_protocol']) && $this->options['http_protocol'] == 'https') {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0); // 信任任何证书
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名
        }
        // 代理配置
        if (!empty($this->options['proxy_host']) && !empty($this->options['proxy_port'])) {
            $this->useProxy = true;
            curl_setopt($this->ch, CURLOPT_PROXY, $this->options['proxy_host']);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $this->options['proxy_port']);
        }
        // 使用默认配置
        if (!empty($this->options['proxy_cluster']) && isset($this->proxyInfos[$this->options['proxy_cluster']])) {
            $this->useProxy = true;
            $proxyInfo = $this->proxyInfos[$this->options['proxy_cluster']];
            curl_setopt($this->ch, CURLOPT_PROXY, $proxyInfo['host']);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $proxyInfo['port']);
        }

        if (isset($this->options['follow_location'])) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        if (isset($this->options['cookie'])) {
            curl_setopt(
                $this->ch,
                CURLOPT_USERAGENT,
                // 'Mozilla/5.0 (Windows NT 6.1; WOW64) ' .
                // 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36'
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) ' .
                'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'
            );
            curl_setopt($this->ch, CURLOPT_COOKIE, $this->options['cookie']);
        }

        if (isset($this->options['timeout'])) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->options['timeout']);
        }
    }

    //获得url
    public function getUrl()
    {
        return $this->url;
    }

    public function getStatus()
    {
        return curl_getinfo($this->ch);
    }

    public function getResponseHeader()
    {
        return $this->responseHeaders;
    }

    public function getResponseCookie()
    {
        return $this->responseCookies;
    }

    /**
     * 根据传入的参数发起请求
     * @param array|string 参数。Get/Post请求要求传数组。传字符串或其他原始报文请用其他 REQUEST_TYPE_*
     * @param int $times 请求次数，可选，默认为构造函数中设置的
     * @return array($state, $data)
     */
    public function fetch($params, $times = null)
    {
        $times = $times ? $times : $this->maxTimes;
        $this->requestInit();
        [$state, $data] = $this->process($params);
        $times--;
        while ($state == self::FAILER && $times) {
            [$state, $data] = $this->process($params);
            $times--;
        }
        $this->state = $state;
        $this->response = $data;

        if ($state == self::FAILER) {
            return [false, $data];
        }
        return [true, $data];
    }


    /**
     * 根据传入的参数发起一次请求
     * @param array|string $params 参数
     * @return array($state, $data)
     */
    public function fetchOnce($params)
    {
        return $this->fetch($params, 1);
    }

    /**
     * @return array
     * @deprecated 不再使用
     * 获得请求结果
     */
    public function getResponse()
    {
        if ($this->state == self::SUCCESS) {
            return [true, $this->response];
        } else {
            return [false, $this->response];
        }
    }


    /**
     * 释放curl资源
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->state == self::SUCCESS;
    }

    /**
     * 释放curl资源
     */
    public function release()
    {
        if (!empty($this->ch)) {
            curl_close($this->ch);
        }
    }


    /**
     * 析构函数，释放curl资源
     */
    public function __destruct()
    {
        $this->release();
    }

    /**
     * 根据传入的参数处理请求
     * @param string|array 参数
     * @return array [state, data] 状态和数据
     */
    protected function process($params)
    {
        $url = $this->getUrl();
        switch ($this->type) {
            case self::REQUEST_TYPE_GET:
                return $this->get($url, $params);
            case self::REQUEST_TYPE_POST:
                return $this->post($url, $params);
            case self::REQUEST_TYPE_POST_RAW_DATA:
                return $this->postRawData($url, $params);
            case self::REQUEST_TYPE_TK_GET:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
                return $this->get($url, $params);
            case self::REQUEST_TYPE_TK_POST:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
                if (is_array($params)) {
                    $params = json_encode($params);
                }
                return $this->requestRawData($url, $params);
            case self::REQUEST_TYPE_GOOGLE_POST:
                $post_data = is_array($params) ? http_build_query($params) : $params;
                return $this->postRawData($url, $post_data);
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->originType);
                return $this->requestRawData($url, $params);
        }
    }

    /**
     * 发起get请求
     * @param string $url 地址
     * @param array $params 参数
     * @return array [state, data] 状态和数据
     */
    protected function get($url, array $params = []): array
    {
        $getUrl = $url . (strpos($url, '?') === false ? '?' : '') . $this->encodeParams($params);
        curl_setopt($this->ch, CURLOPT_URL, $getUrl);
        if (isset($this->options['headers'])) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->options['headers']);
        }
        $result = curl_exec($this->ch);
        if ($result === false) {
            return [self::FAILER, curl_error($this->ch)];
        } else {
            return [self::SUCCESS, $this->parseResponse($result)];
        }
    }

    /**
     * 发起post请求
     * @param string $url 地址
     * @param array|string $params 参数
     * @return array [state, data] 状态和数据
     */
    protected function post($url, $params = [])
    {
        return $this->postRawData($url, $this->encodeParams($params));
    }

    protected function postRawData($url, $params)
    {
        curl_setopt($this->ch, CURLOPT_POST, true);
        return $this->requestRawData($url, $params);
    }


    protected function requestRawData($url, $data)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }

        if (isset($this->options['headers'])) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->options['headers']);
        }

        $result = curl_exec($this->ch);
        if ($result === false) {
            return [self::FAILER, curl_error($this->ch)];
        } else {
            return [self::SUCCESS, $this->parseResponse($result)];
        }
    }

    private function parseResponse($response)
    {
        // 如果需要返回响应头则尝试解析
        $delimiterOfHeaderAndBody = "\r\n\r\n";
        if ($this->options['fetchResponseHeader'] ?? false === true) {
            $responseList = explode($delimiterOfHeaderAndBody, $response);
            if (!$this->startWith($responseList[0], 'HTTP')) { // 认为header第一行必定是HTTP开头
                return $response;
            }
            // parse header
            $rawHeaderList = explode("\r\n", $responseList[0]);
            $headerList = [];
            foreach ($rawHeaderList as $headerItem) {
                $item = explode(': ', $headerItem);
                if (count($item) >= 2) {
                    $key = $item[0];
                    unset($item[0]);
                    $value = implode(': ', $item);

                    // 解析cookie
                    if (strtolower($key) === 'set-cookie') {
                        $this->responseCookies[] = $value;
                    } else {
                        $headerList[$key] = $value;
                    }
                } else {
                    $headerList[] = $headerItem;
                }
            }
            $this->responseHeaders = $headerList;
            unset($responseList[0]);

            return implode($delimiterOfHeaderAndBody, $responseList);
        }
        return $response;
    }

    public function encodeParams($params)
    {
        if (empty($params)) {
            return '';
        }
        if (is_string($params)) {
            return $params;
        }
        $pa = [];
        foreach ($params as $key => $value) {
            $value = rawurlencode($value);
            $pa[] = $key . '=' . $value;
        }
        return implode('&', $pa);
    }

    public function startWith($str, $startStr): bool
    {
        $len = strlen($startStr);
        return (substr($str, 0, $len) === $startStr);
    }
}
