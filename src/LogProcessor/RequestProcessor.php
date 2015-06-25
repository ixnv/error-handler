<?php

namespace eLama\ErrorHandler\LogProcessor;

class RequestProcessor
{
    const LENGTH = 8;

    /** @var string */
    private $requestId;

    public function __construct()
    {
        $this->requestId = substr(hash('md5', uniqid('', true)), 0, self::LENGTH);
    }

    public function __invoke(array $record)
    {
        $record['extra']['requestId'] = $this->requestId;

        return $record;
    }
}
