<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\Matcher\Matcher;
use eLama\ErrorHandler\ResponseRenderer\ResponseRenderer;
use eLama\ErrorHandler\ResponseRenderer\WebResponseRendererFactory;
use Psr\Log\LoggerInterface;

class ErrorHandlerContainer
{
    /**
     * @var ErrorHandler
     */
    private static $errorHandler;

    private function __construct() {}

    /**
     * @return ErrorHandler
     */
    public static function getErrorHandler()
    {
        if (!static::$errorHandler) {
            echo 'First you must call ErrorHandlerContainer::init';
            die();
        }

        return static::$errorHandler;
    }

    /**
     * @param string $errorHandlerLogPath
     * @param Matcher[] $matchers
     * @param bool $debugMode
     * @param LoggerInterface $logger
     */
    public static function init(
        $errorHandlerLogPath,
        array $matchers,
        $debugMode = false,
        LoggerInterface $logger = null
    ) {
        if (static::$errorHandler) {
            throw new \LogicException('ErrorHandler is already initialized');
        }

        if (!$logger) {
            $logger = LoggerFactory::createLogger($errorHandlerLogPath);
        }

        $responseRenderer = self::createResponseRenderer();

        static::$errorHandler = new ErrorHandler(
            $logger,
            new ErrorCodesCatalog(),
            $matchers,
            self::createLogLastFatalError($errorHandlerLogPath),
            $debugMode,
            $responseRenderer
        );

        // TODO[a.shirikov, b6x] Вынести логику ниже в конструктор (или лучше register метод) ErrorHandler'а
        if (ob_get_level() < 1) {
            ob_start();
        }

        while (self::getCurrentErrorHandler() !== null) {
            restore_error_handler();
        }

        set_error_handler([static::$errorHandler, 'handleError'], E_ALL & (~E_NOTICE) | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
        set_exception_handler([static::$errorHandler, 'handleException']);

        $errorHandler = static::$errorHandler;
        register_shutdown_function([$errorHandler, 'onShutdown']);
    }

    /**
     * @param string $logDirPath
     * @return callable
     */
    private static function createLogLastFatalError($logDirPath)
    {
        return function ($fatalError) use ($logDirPath) {
            $file = $logDirPath . '/last_fatal_errors.log';
            $fh = fopen($file, 'c+');

            $contents = filesize($file) > 0 ? fread($fh, filesize($file)) : '';

            $contents .= '#--- ' . date('c') . ' ---' . PHP_EOL;
            $contents .= var_export($fatalError, true);
            $contents = preg_replace('/.*?#(.{5,4096})$/us', '#$1', $contents);

            ftruncate($fh, 0);
            rewind($fh);

            fwrite($fh, $contents);
            fclose($fh);
        };
    }

    /**
     * @return ResponseRenderer
     */
    private static function createResponseRenderer()
    {
        if (self::isConsole()) {
            return null;
        }

        $webResponseRendererFactory = WebResponseRendererFactory::createFromGlobals();

        return $webResponseRendererFactory->createResponseRenderer();
    }

    /**
     * @return bool
     */
    private static function isConsole()
    {
        return php_sapi_name() == 'cli';
    }

    private static function getCurrentErrorHandler()
    {
        $current = set_error_handler('var_dump');
        restore_error_handler();

        return $current;
    }
}
