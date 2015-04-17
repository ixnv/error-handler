<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorEvent;

/**
 * @deprecated Use CodeMessageMatcher instead (since 17.02.2015)
 * @see CodeMessageMatcher
 */
class MessageMatcher implements Matcher
{
    /**
     * @var
     */
    private $messagePart;
    /**
     * @var
     */
    private $actionIfMatches;

    public function __construct($messagePart, $actionIfMatches)
    {
        $this->messagePart = $messagePart;
        $this->actionIfMatches = $actionIfMatches;
    }

    /**
     * @param ErrorEvent $event
     * @return bool One of constants: HANDLE, NO_MATCH or IGNORE
     */
    public function match(ErrorEvent $event)
    {
        return (strpos($event->getMessage(), $this->messagePart) !== false)  ? $this->actionIfMatches : self::NO_MATCH;
    }
}
