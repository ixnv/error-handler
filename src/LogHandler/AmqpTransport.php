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
    const EXCHANGE_NAME = 'log-messages';

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
            self::EXCHANGE_NAME
        );

        return 1;
    }
}
