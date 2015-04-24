<?php

namespace eLama\ErrorHandler;

class ErrorCodesCatalog
{
    const TYPE_EXCEPTION = 'Exception';

    private $errorTypeMap = [
        E_ERROR                => 'E_ERROR',
        E_WARNING              => 'E_WARNING',
        E_PARSE                => 'E_PARSE',
        E_NOTICE               => 'E_NOTICE',
        E_CORE_ERROR           => 'E_CORE_ERROR',
        E_CORE_WARNING         => 'E_CORE_WARNING',
        E_COMPILE_ERROR        => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING      => 'E_COMPILE_WARNING',
        E_USER_ERROR           => 'E_USER_ERROR',
        E_USER_WARNING         => 'E_USER_WARNING',
        E_USER_NOTICE          => 'E_USER_NOTICE',
        E_STRICT               => 'E_STRICT',
        E_RECOVERABLE_ERROR    => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED           => 'E_DEPRECATED',
        E_USER_DEPRECATED      => 'E_USER_DEPRECATED',
        E_ALL                  => 'E_ALL',
        self::TYPE_EXCEPTION   => 'UNCAUGHT_EXCEPTION',
    ];

    private $logLevelMap = [
        E_ERROR                => 'CRITICAL',
        E_WARNING              => 'WARNING',
        E_PARSE                => 'CRITICAL',
        E_NOTICE               => 'NOTICE',
        E_CORE_ERROR           => 'CRITICAL',
        E_CORE_WARNING         => 'WARNING',
        E_COMPILE_ERROR        => 'CRITICAL',
        E_COMPILE_WARNING      => 'WARNING',
        E_USER_ERROR           => 'ERROR',
        E_USER_WARNING         => 'WARNING',
        E_USER_NOTICE          => 'NOTICE',
        E_STRICT               => 'NOTICE',
        E_RECOVERABLE_ERROR    => 'CRITICAL',
        E_DEPRECATED           => 'NOTICE',
        E_USER_DEPRECATED      => 'NOTICE',
        self::TYPE_EXCEPTION   => 'CRITICAL',
    ];

    /**
     * @param int $errorCode
     * @return string
     */
    public function getErrorTypeToString($errorCode)
    {
        return $this->errorTypeMap[$errorCode];
    }

    /**
     * @param string $errorCode
     * @return int
     */
    public function getErrorCodeFromString($errorCode)
    {
        return array_search($errorCode, $this->errorTypeMap);
    }

    /**
     * @param int|string $errorCode
     * @return bool
     */
    public function isException($errorCode)
    {
        return $errorCode === static::TYPE_EXCEPTION;
    }

    /**
     * @param int|string $errorCode
     * @return bool
     */
    public function isIndicatesCodeCompatibility($errorCode)
    {
        return in_array(
            $errorCode,
            [
                E_STRICT,
                E_DEPRECATED,
                E_USER_DEPRECATED
            ]
        );
    }

    /**
     * @param int|string $errorCode
     * @return bool
     */
    public function isUserGeneratedError($errorCode)
    {
        return in_array($errorCode, [
            E_USER_ERROR,
            E_USER_WARNING,
            E_USER_NOTICE,
            E_USER_DEPRECATED
        ]);
    }

    /**
     * @param int|string $errorCode
     * @return bool
     */
    public function isFatalError($errorCode)
    {
        return in_array((string)$errorCode, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR,
        ]);
    }

    /**
     * @param int|string $errorCode
     * @return string
     */
    public function getLogLevel($errorCode)
    {
        return (isset($this->logLevelMap[$errorCode])) ? $this->logLevelMap[$errorCode] : 'ERROR';
    }
}
