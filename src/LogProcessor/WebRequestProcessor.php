<?php

namespace eLama\ErrorHandler\LogProcessor;

class WebRequestProcessor
{
    private $serverData;

    private $extraFields = [
        'url'         => 'REQUEST_URI',
        'ip'          => 'REMOTE_ADDR',
        'http_method' => 'REQUEST_METHOD',
        'server'      => 'SERVER_NAME',
    ];

    /**
     * @param array|\ArrayAccess $serverData  Array or object w/ ArrayAccess that provides access to the $_SERVER data
     */
    public function __construct($serverData = null)
    {
        if (null === $serverData) {
            $this->serverData = $_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
        }
    }

    public function __invoke(array $record)
    {
        // skip processing if for some reason request data
        // is not present (CLI or wonky SAPIs)
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }

        $record['extra']['request'] = [];

        $record['extra']['request'] = $this->appendExtraFields($record['extra']['request']);

        if (!empty($_GET)) {
            $record['extra']['request']['get'] = $_GET;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'GET') {
            $record['extra']['request']['post'] = $_POST;
        }

        $record['extra']['request']['headers'] = $this->getHttpHeaders();

        // TODO[6ex] Когда перейдем на версию PHP 5.6 нужно убрать проверку на POST
        if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && $input = file_get_contents('php://input')) {
            $record['extra']['request']['input'] = $input;
        }

        return $record;
    }

    /**
     * @param  array $extra
     * @return array
     */
    private function appendExtraFields(array $extra)
    {
        foreach ($this->extraFields as $extraName => $serverName) {
            $extra[$extraName] = isset($this->serverData[$serverName]) ? $this->serverData[$serverName] : null;
        }

        if (isset($this->serverData['UNIQUE_ID'])) {
            $extra['unique_id'] = $this->serverData['UNIQUE_ID'];
        }

        return $extra;
    }

    private function getHttpHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $httpHeaders = [];
        foreach ($this->serverData as $key => $value) {
            if (strpos(strtolower($key), 'http_') === 0) {
                $headerName = substr($key, 5);
                $httpHeaders[$headerName] = $value;
            }
        }

        return $httpHeaders;
    }
}
