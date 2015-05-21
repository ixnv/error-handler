<?php

namespace eLama\ErrorHandler;

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
        return self::$elkHandler;
    }
}
