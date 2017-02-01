<?php

namespace eLama\ErrorHandler\LogHandler;

use Gelf\Encoder\EncoderInterface;
use Gelf\MessageInterface as Message;
use Gelf\Transport\AbstractTransport;
use PhpAmqpLib\Channel\AMQPChannel;
use Gelf\Encoder\JsonEncoder as DefaultEncoder;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpTransport extends AbstractTransport
{
    const ROUTING_KEY = 'log-messages';
    const EXCHANGE_NAME = 'log-exchange';

    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var EncoderInterface
     */
    protected $messageEncoder;

    /**
     * @param AbstractConnection $connection
     * @param $queueName
     */
    public function __construct(AbstractConnection $connection, $queueName)
    {
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->messageEncoder = new DefaultEncoder();
    }

    /**
     * @param Message $message
     * @return int
     */
    public function send(Message $message)
    {
        try {
            if ($this->channel === null) {
                $this->channel = $this->connection->channel();
                $this->channel->queue_declare($this->queueName, false, true, false, false);
            }

            $rawMessage = $this->getMessageEncoder()->encode($message);

            $this->channel->basic_publish(
                new AMQPMessage(
                    $rawMessage,
                    [
                        'delivery_mode' => 2,
                        'content_type' => 'application/json'
                    ]
                ),
                self::EXCHANGE_NAME,
                self::ROUTING_KEY
            );

            return 1;
        } catch (AMQPRuntimeException $exception) {
            return 0;
        }
    }
}
