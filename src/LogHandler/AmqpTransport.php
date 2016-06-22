<?php

namespace eLama\ErrorHandler\LogHandler;

use Gelf\Encoder\EncoderInterface;
use Gelf\MessageInterface as Message;
use Gelf\Transport\AbstractTransport;
use PhpAmqpLib\Channel\AMQPChannel;
use Gelf\Encoder\JsonEncoder as DefaultEncoder;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpTransport extends AbstractTransport
{
    const ROUTING_KEY = 'log-messages';
    const EXCHANGE_NAME = 'log-exchange';

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var EncoderInterface
     */
    protected $messageEncoder;

    /**
     * @param AMQPChannel $channel
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->messageEncoder = new DefaultEncoder();
    }

    /**
     * @param Message $message
     * @return int
     */
    public function send(Message $message)
    {
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
    }
}
