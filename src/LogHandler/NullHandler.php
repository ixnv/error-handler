<?php

namespace eLama\ErrorHandler\LogHandler;

use Monolog\Handler\AbstractHandler;

class NullHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        return false;
    }
}
