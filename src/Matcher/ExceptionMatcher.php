<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorCodesCatalog;
use eLama\ErrorHandler\ErrorEvent;

class ExceptionMatcher implements Matcher
{
    /** @var ErrorCodesCatalog */
    private $errorCodesCatalog;

    public function __construct(ErrorCodesCatalog $errorCodesCatalog)
    {
        $this->errorCodesCatalog = $errorCodesCatalog;
    }

    public function match(ErrorEvent $event)
    {
        return $this->errorCodesCatalog->isException($event->getCode()) ? Matcher::HANDLE : Matcher::NO_MATCH;
    }
}
