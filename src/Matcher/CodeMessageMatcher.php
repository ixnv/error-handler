<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorEvent;

class CodeMessageMatcher implements Matcher
{
    /**
     * @var
     */
    private $messagePart;
    /**
     * @var
     */
    private $actionIfMatches;
    /**
     * @var
     */
    private $code;

    /**
     * @param int|string $code
     * @param string $messagePart
     * @param int $actionIfMatches
     */
    public function __construct($code, $messagePart, $actionIfMatches)
    {
        $this->code = $code;
        $this->messagePart = $messagePart;
        $this->actionIfMatches = $actionIfMatches;
    }

    /**
     * @param ErrorEvent $event
     * @return bool One of constants: HANDLE, NO_MATCH or IGNORE
     */
    public function match(ErrorEvent $event)
    {
        return (strpos($event->getMessage(), $this->messagePart) !== false && $this->code === $event->getCode())  ? $this->actionIfMatches : self::NO_MATCH;
    }
}
