<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorEvent;

class UnknownFileMatcher implements Matcher
{
    /**
     * @var
     */
    private $actionOnMatch;

    public function __construct($actionOnMatch = self::HANDLE)
    {
        $this->actionOnMatch = $actionOnMatch;
    }

    /**
     * {@inheritdoc}
     */
    public function match(ErrorEvent $event)
    {
        return (strtolower($event->getFile()) === 'unknown') ? $this->actionOnMatch : self::NO_MATCH;
    }
}
