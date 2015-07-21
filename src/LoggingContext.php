<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\LogHandler\NullHandler;
use Monolog\Handler\HandlerInterface;

/**
 * TODO[a.shirikov] Separate ErrorHandler / Logging packages ?
 */
class LoggingContext
{
    private static $elkHandler = null;

    /**
     * @param HandlerInterface $handler
     */
    public static function setHandler(HandlerInterface $handler)
    {
        self::$elkHandler = $handler;
    }

    /**
     * @return HandlerInterface
     */
    public static function getHandler()
    {
        if (!self::$elkHandler) {
            self::$elkHandler = new NullHandler();
        }

        return self::$elkHandler;
    }
}
