<?php
/**
 * User: Liu Xing
 * Date: 2019/3/29 0029
 * Time: 10:37
 */

namespace App\Libs\RabbitMq;

use AMQPQueue;
use AMQPException;
use App\Lib\ChuanglanSmsApi;
use Exception;
use Illuminate\Support\Facades\Log;
use Error;

/**
 * rabbitMq 消费者基类
 * Class Producer
 * @package Library
 */
class BaseConsumer
{
    use RabbitMqConnect;

    protected $queueName = ''; // 队列名

    protected $routeKey = ''; // 路由key

    protected $queue = null;

    public function __construct()
    {
        $this->connect();
        $this->declareExchange();
        $this->declareQueue();
        $this->bootstrapLog();
    }

    /**
     * 配置声明队列并确定绑定关系
     */
    protected function declareQueue()
    {
        try {
            if ($this->queueName === '') {
                throw new Exception('Queue name must be set');
            }

            $this->queue = new AMQPQueue($this->channel);
            $this->queue->setName($this->queueName);
            $this->queue->setFlags(AMQP_DURABLE);
            $this->queue->declareQueue();
            $this->queue->bind($this->exchangeName, $this->routeKey);
        } catch (Exception $e) {

        }
    }

    public function consume($retry = 6)
    {
        while (true) {
            try {
                // 从队列中取出下一条消息，如果没有消息则挂起1秒
                if ($envelope = $this->queue->get()) {
                    $this->handle($envelope, $this->queue);
                } else {
                    sleep(1);
                }
            } catch (AMQPException $e) {
                if ($retry-- > 0) {
                    sleep(20);
                    $this->__construct();
                    $this->consume($retry);
                } else {
                    $sms = new ChuanglanSmsApi();
                    $sms->sendSMS('15088720891', __CLASS__ . ' 遇到异常关闭请前往检查');
                    throw $e;
                }
            }
        }
    }

    public function handle($envelope, $queue)
    {
        $message = $envelope->getBody();
        /*
         * 处理消息，如果消息体是json数据，则格式化后处理
         */
        $data = unserialize($message);

        try {
            $this->handleMessage($data);
        } catch (Error $exception) {
            $this->logException($exception, $message);
        } catch (Exception $exception) {
            $this->logException($exception, $message);
        }

        /**
         * 手动应答成功，如果开启了自动应答则不用
         */
        $queue->ack($envelope->getDeliveryTag());
    }

    /**
     * 处理消息，子类需重写此函数
     * @param $message
     */
    protected function handleMessage($message)
    {

    }

    /**
     * 遇到异常做记录
     * @param Exception $e
     */
    protected function logException($e, $data)
    {
        $log = implode("\n", [
            'message' => "Message: " . $e->getMessage(),
            'file'    => "File: " . $e->getFile(),
            'line'    => "Line: " . $e->getLine(),
            'data'    => "Data: " . $data
        ]);
        Log::error($this->queueName . " exception\n{$log}");
    }

    protected function bootstrapLog()
    {
        Log::getMonolog()->popHandler();
        //leshu修改配置文件地址
        Log::useDailyFiles(config('log.log_path') . '/mq/rabbit.log');
    }
}
