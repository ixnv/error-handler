<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\LogHandler\NullHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Gelf\Publisher;
use Gelf\Transport\AmqpTransport;
use Monolog\Handler\GelfHandler;

class CentralizedLoggerFactory
{
    /**
     * @param string $name
     * @param AmqpSettings $amqpSettings
     * @return Logger
     */
    public static function createLogger($name, AmqpSettings $amqpSettings)
    {
        $logger = new Logger($name);

        $handler = self::createGraylogHandler($amqpSettings);
        LoggingContext::setHandler($handler);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @param AmqpSettings $amqpSettings
     * @return HandlerInterface
     */
    private static function createGraylogHandler(AmqpSettings $amqpSettings)
    {
        try {
            $connection = new \AMQPConnection([
                'host' => $amqpSettings->getHost(),
                'login' => $amqpSettings->getLogin(),
                'password' => $amqpSettings->getPassword()
            ]);

            $connection->connect();

            $channel = new \AMQPChannel($connection);
            $exchange = new \AMQPExchange($channel);

            $exchange->setName($amqpSettings->getExchangeName());
            $exchange->setType(AMQP_EX_TYPE_FANOUT);
            $exchange->declareExchange();

            $queue = new \AMQPQueue($channel);
            $queue->setName($amqpSettings->getQueueName());
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();
            $queue->bind($exchange->getName());

            return new GelfHandler(new Publisher(new AmqpTransport($exchange, $queue)));
        } catch (\AMQPConnectionException $e) {
            return new NullHandler();
        }
    }
}
