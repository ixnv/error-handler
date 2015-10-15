<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\LogHandler\AmqpTransport;
use eLama\ErrorHandler\LogHandler\NullHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Gelf\Publisher;
use Monolog\Handler\GelfHandler;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class CentralizedLoggerFactory
{
    /**
     * @var HandlerInterface
     */
    private static $handler;

    /**
     * @param string $name
     * @param AmqpSettings $amqpSettings
     * @return Logger
     */
    public static function createLogger($name, AmqpSettings $amqpSettings)
    {
        $logger = new Logger($name);
        $handler = self::getGraylogHandler($amqpSettings);
        $logger->pushHandler($handler);
        LoggingContext::setHandler($handler);

        return $logger;
    }

    /**
     * @param AmqpSettings $amqpSettings
     * @return HandlerInterface
     */
    private static function getGraylogHandler(AmqpSettings $amqpSettings)
    {
        if (!self::$handler) {
            self::$handler = self::createGraylogHandler($amqpSettings);
        }

        return self::$handler;
    }

    /**
     * @param AmqpSettings $amqpSettings
     * @return HandlerInterface
     */
    private static function createGraylogHandler(AmqpSettings $amqpSettings)
    {
        try {
            $connection = new AMQPStreamConnection(
                $amqpSettings->getHost(),
                $amqpSettings->getPort(),
                $amqpSettings->getLogin(),
                $amqpSettings->getPassword()
            );

            $channel = $connection->channel();
            $channel->queue_declare($amqpSettings->getQueueName(), false, true, false, false);

            return new GelfHandler(new Publisher(new AmqpTransport($channel)));
        } catch (AMQPRuntimeException $e) {
            return new NullHandler();
        }
    }
}
