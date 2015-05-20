<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\LogHandler\MailerFormatter;
use eLama\ErrorHandler\LogProcessor\ConsoleProcessor;
use eLama\ErrorHandler\LogProcessor\ContextNameProcessor;
use eLama\ErrorHandler\LogProcessor\SessionProcessor;
use eLama\ErrorHandler\LogProcessor\WebRequestProcessor;
use eLama\ErrorHandler\LogHandler\MessageHtmlFormatter;
use eLama\ErrorHandler\LogHandler\MailHandlerFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class LoggerFactory
{
    const ENV_WEB = 'web';
    const ENV_CLI = 'cli';

    /**
     * @param string $logDirectory
     * @param string $environment `web` or `cli`
     * @param bool $debug
     * @return Logger
     */
    public function createLogger($logDirectory, $environment, $debug)
    {
        $this->prepareLogDir($logDirectory);
        $logDirectory = realpath($logDirectory);
        $this->preloadClasses();

        $logger = new Logger('ErrorHandler');
        $this->setBasicHandlers($logger, $logDirectory);
        $this->setOutputHandlers($logger, $environment, $debug);
        $this->setLogProcessors($logger);

        return $logger;
    }

    /**
     * @param Logger $logger
     * @param string $logDirectory
     */
    private function setBasicHandlers(Logger $logger, $logDirectory)
    {
        $fileHandler = new RotatingFileHandler($logDirectory . '/log.log');
        $logger->pushHandler($fileHandler);

        $mailHandler = (new MailHandlerFactory)->createMailHandler();
        $mailHandler->setFormatter(new MailerFormatter());
        $logger->pushHandler($mailHandler);
    }

    private function prepareLogDir($logDirectory)
    {
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        } elseif (!is_dir($logDirectory)) {
            echo __FILE__ . ':' . __LINE__ . " Log directory path is not a path to a directory!";
            die;
        }
    }

    private function preloadClasses()
    {
        //Preloading classes in case of E_STRICT
        class_exists('Monolog\Formatter\LineFormatter');
        class_exists('Swift_Events_SendEvent');
        class_exists('Swift_CharacterReader_Utf8Reader');
    }

    private function setLogProcessors(Logger $logger)
    {
        $logger->pushProcessor(new ContextNameProcessor());
        $logger->pushProcessor(new ConsoleProcessor);
        $logger->pushProcessor(new SessionProcessor);
        $logger->pushProcessor(new WebRequestProcessor($_SERVER));
    }

    /**
     * @param Logger $logger
     * @param string $environment
     * @param bool $debug
     */
    private function setOutputHandlers(Logger $logger, $environment, $debug)
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
}
