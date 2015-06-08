<?php

namespace eLama\ErrorHandler;

use AMQPExchange;
use Gelf\Encoder\JsonEncoder as DefaultEncoder;
use Gelf\MessageInterface as Message;
use Gelf\Transport\AbstractTransport;

class AmqpTransport extends AbstractTransport
{
    /**
     * @var AMQPExchange $exchange
     */
    protected $exchange;

    /**
     * @param AMQPExchange $exchange
     * @param string $queueName
     */
    public function __construct(AMQPExchange $exchange, $queueName = 'log-messages')
    {
        $this->messageEncoder = new DefaultEncoder();
        $this->queueName = $queueName;
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     */
    public function send(Message $message)
    {
        $rawMessage = $this->getMessageEncoder()->encode($message);
        $this->exchange->publish(
            $rawMessage,
            $this->queueName,
            AMQP_NOPARAM,
            [
                'delivery_mode' => 2,
                'Content-type' => 'application/json'
            ]
        );
        return 1;
    }
}
