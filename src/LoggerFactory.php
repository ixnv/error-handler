<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\LogHandler\ElasticSearchFormatter;
use eLama\ErrorHandler\LogHandler\MessageHtmlFormatter;
use eLama\ErrorHandler\LogProcessor\ConsoleProcessor;
use eLama\ErrorHandler\LogProcessor\SessionProcessor;
use eLama\ErrorHandler\LogProcessor\WebRequestProcessor;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    const ENV_WEB = 'web';
    const ENV_CLI = 'cli';

    /**
     * @param string $logDirectory
     * @return Logger
     */
    public static function createLogger($logDirectory)
    {
        $environment = self::isConsole() ? self::ENV_CLI : self::ENV_WEB;

        self::prepareLogDir($logDirectory);
        $logDirectory = realpath($logDirectory);
        self::preloadClasses();

        $logger = new Logger('ErrorHandler');
        self::setBasicHandlers($logger, $logDirectory);
        self::setOutputHandlers($logger, $environment, self::isConsole());
        self::setLogProcessors($logger);

        return $logger;
    }

    /**
     * @param Logger $logger
     * @param string $logDirectory
     */
    private static function setBasicHandlers(Logger $logger, $logDirectory)
    {
        $fileHandler = new RotatingFileHandler($logDirectory . '/log.log');
        $logger->pushHandler($fileHandler);
    }

    private static function prepareLogDir($logDirectory)
    {
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        } elseif (!is_dir($logDirectory)) {
            echo __FILE__ . ':' . __LINE__ . " Log directory path is not a path to a directory!";
            die;
        }
    }

    private static function preloadClasses()
    {
        //Preloading classes in case of E_STRICT
        class_exists('Monolog\Formatter\LineFormatter');
    }

    private static function setLogProcessors(Logger $logger)
    {
        $logger->pushProcessor(new ConsoleProcessor);
        $logger->pushProcessor(new SessionProcessor);
        $logger->pushProcessor(new WebRequestProcessor($_SERVER));
    }

    /**
     * @param Logger $logger
     * @param string $environment
     * @param bool $debug
     */
    private static function setOutputHandlers(Logger $logger, $environment, $debug)
    {
        if ($environment == self::ENV_CLI) {
            $streamHandler = new StreamHandler(STDERR);
            $logger->pushHandler($streamHandler);
        } elseif ($debug) {
            $outputHandler = new StreamHandler(fopen('php://output', 'a'));
            $outputHandler->setFormatter(new MessageHtmlFormatter);
            $logger->pushHandler($outputHandler);
        }
    }

    /**
     * @return bool
     */
    private static function isConsole()
    {
        return php_sapi_name() == 'cli';
    }
}
