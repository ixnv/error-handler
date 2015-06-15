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

    public static function setElkHandler(HandlerInterface $handler)
    {
        self::$elkHandler = $handler;
    }

    public static function getElkHandler()
    {
        if (!self::$elkHandler) {
            self::$elkHandler = new NullHandler();
        }

        return self::$elkHandler;
    }
}
