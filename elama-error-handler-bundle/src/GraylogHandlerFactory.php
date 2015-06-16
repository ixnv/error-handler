<?php

namespace eLama\ErrorHandler\Bundle;

use eLama\ErrorHandler\LoggingContext;
use eLama\ErrorHandler\LogHandler\NullHandler;
use Gelf\Publisher;
use Gelf\Transport\AmqpTransport;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GraylogHandlerFactory
{
    const DEFAULT_EXCHANGE_NAME = 'log-messages';
    const DEFAULT_QUEUE_NAME = 'log-messages';

    /**
     * @param ContainerInterface $container
     * @return HandlerInterface
     */
    public static function create(ContainerInterface $container)
    {
        $handler = new NullHandler();

        if (self::isEnabled($container)) {
            $options = $container->getParameter('graylog_logging');

            try {
                $connection = new \AMQPConnection([
                    'host' => $options['host'],
                    'login' => $options['login'],
                    'password' => $options['password']
                ]);

                $connection->connect();

                $channel = new \AMQPChannel($connection);
                $exchange = new \AMQPExchange($channel);

                $exchange->setName(self::getExchangeName($options));
                $exchange->setType(AMQP_EX_TYPE_FANOUT);
                $exchange->declareExchange();

                $queue = new \AMQPQueue($channel);
                $queue->setName(self::getQueueName($options));
                $queue->setFlags(AMQP_DURABLE);
                $queue->declareQueue();
                $queue->bind($exchange->getName());

            } catch (\AMQPConnectionException $e) {
                return $handler;
            }

            $handler = new GelfHandler(
                new Publisher(new AmqpTransport($exchange, $queue))
            );

            LoggingContext::setElkHandler($handler);
        }

        return $handler;
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
    private static function getExchangeName(array $options)
    {
        return array_key_exists('exchange_name', $options) ? $options['exchange_name'] : self::DEFAULT_EXCHANGE_NAME;
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
