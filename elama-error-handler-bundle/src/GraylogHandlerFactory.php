<?php

namespace eLama\ErrorHandler\Bundle;

use eLama\ErrorHandler\AmqpSettings;
use eLama\ErrorHandler\LoggingContext;
use eLama\ErrorHandler\LogHandler\AmqpTransport;
use eLama\ErrorHandler\LogHandler\GraylogFormatter;
use eLama\ErrorHandler\LogHandler\NullHandler;
use Gelf\Publisher;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GraylogHandlerFactory
{
    const DEFAULT_QUEUE_NAME = 'log-messages';

    /**
     * @var HandlerInterface
     */
    private static $handler;

    /**
     * @param ContainerInterface $container
     * @return HandlerInterface
     */
    public static function create(ContainerInterface $container)
    {
        $handler = new NullHandler();

        if (self::isEnabled($container)) {
            $options = $container->getParameter('graylog_logging');

            $handler = self::getGraylogHandler(new AmqpSettings(
                $options['host'],
                $options['port'],
                $options['login'],
                $options['password'],
                self::getQueueName($options)
            ));

            LoggingContext::setHandler($handler);
        }

        return $handler;
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

            $handler = new GelfHandler(new Publisher(new AmqpTransport($channel)));
            $handler->setFormatter(new GraylogFormatter());
            return $handler;
        } catch (AMQPRuntimeException $e) {
            return new NullHandler();
        }
    }

    /**
     * @param ContainerInterface $container
     * @return bool
     */
    private static function isEnabled(ContainerInterface $container)
    {
        $enabled = false;

        if ($container->hasParameter('graylog_logging')) {
            $options = $container->getParameter('graylog_logging');
            $enabled = $options['enabled'];
        }

        return $enabled;
    }

    /**
     * @param string[] $options
     * @return string
     */
    private static function getQueueName(array $options)
    {
        return array_key_exists('queue_name', $options) ? $options['queue_name'] : self::DEFAULT_QUEUE_NAME;
    }
}
