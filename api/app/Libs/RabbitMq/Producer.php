<?php
/**
 * User: Liu Xing
 * Date: 2019/3/27 0027
 * Time: 16:20
 */

namespace App\Libs\RabbitMq;

use Illuminate\Support\Facades\Log;

/**
 * rabbitmp 生产者
 * Class Producer
 * @package Library
 */
class Producer
{
    use RabbitMqConnect;

    protected static $instance = null;

    protected static function getInstance($exchangeName)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($exchangeName);
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->connect();
        $this->declareExchange(false);
    }

    /**
     * 向交换机发布消息
     * @param string $exchangeName 交换机
     * @param mixed $message 消息
     * @param string $routeKey 路由键
     */
    public static function publish($exchangeName, $message = '', $routeKey = '')
    {
        try {
            self::getInstance($exchangeName)->exchangeName = $exchangeName;
            self::getInstance($exchangeName)->exchange->setName($exchangeName);
            $message = serialize($message);
            self::getInstance($exchangeName)->exchange->publish($message, $routeKey);
        } catch (\Error $error) {
            Log::info('rabbit publish error', [
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'msg'  => $error->getMessage(),
            ]);
        } catch (\Exception $exception) {
            Log::info('rabbit publish exception', [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'msg'  => $exception->getMessage(),
            ]);
        }
    }
}
