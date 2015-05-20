<?php

namespace eLama\ErrorHandler\Bundle;

use eLama\ErrorHandler\ContextConverter;
use eLama\ErrorHandler\LogHandler\ElasticSearchFormatter;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElkHandlerFactory
{
    const DEFAULT_EXCHANGE_NAME = 'log';
    const DEFAULT_QUEUE_NAME = 'logs';

    /**
     * @param ContainerInterface $container
     * @return HandlerInterface
     */
    public static function create(ContainerInterface $container)
    {
        $handler = new NullHandler();

        if (self::isEnabled($container)) {
            $options = $container->getParameter('elk_logging');
            $exchange = self::createExchangeFromOptions($options);

            $handler = new AmqpHandler($exchange, self::getExchangeName($options));
            $handler->setFormatter(new ElasticSearchFormatter(new ContextConverter()));
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

        if (!$container->hasParameter('elk_logging')) {
            $options = $container->getParameter('elk_logging');

            if ($options['enabled'] === true) {
                $enabled = true;
            }
        }

        return $enabled;
    }

    /**
     * @param string[] $options
     * @return \AMQPExchange
     */
    private static function createExchangeFromOptions(array $options)
    {
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

        return $exchange;
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
