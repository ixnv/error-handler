<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorEvent;

interface Matcher
{
    const HANDLE = 1;
    const NO_MATCH = 0;
    const IGNORE = -1;

    /**
     * @param ErrorEvent $event
     * @return bool One of constants: HANDLE, NO_MATCH or IGNORE
     */
    public function match(ErrorEvent $event);
}
