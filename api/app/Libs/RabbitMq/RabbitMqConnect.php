<?php
/**
 * User: Liu Xing
 * Date: 2019/3/29 0029
 * Time: 18:11
 */

namespace App\Libs\RabbitMq;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use Exception;

trait RabbitMqConnect
{
    protected $config;

    protected $connection;

    protected $channel;

    protected $exchange;

    protected $exchangeName;

    protected $exchangeType;

    protected function connect()
    {
        $connectConfig = config('queue.connections.rabbitmq');
        $this->config = [
            'host'     => $connectConfig['host'],
            'port'     => $connectConfig['port'],
            'login'    => $connectConfig['login'],
            'password' => $connectConfig['password'],
            'vhost'    => $connectConfig['vhost']
        ];

        $this->connection = new AMQPConnection($this->config);
        $this->connection->connect();
        $this->channel = new AMQPChannel($this->connection);
    }

    public function declareExchange($first = true)
    {
        $this->exchange = new AMQPExchange($this->channel);
        if ($first) {
            $this->exchange->setName($this->exchangeName);
            $this->exchange->setType($this->exchangeType);
            $this->exchange->setFlags(AMQP_DURABLE);
            $this->exchange->declareExchange();
        }
    }

    public function __destruct()
    {
        $this->connection->disconnect();
    }
}
