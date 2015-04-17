<?php

namespace eLama\ErrorHandler;

use eLama\ErrorHandler\Matcher\FilePathMatcher;
use eLama\ErrorHandler\Matcher\Matcher;
use eLama\ErrorHandler\ResponseRenderer\ResponseRenderer;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var Matcher[] */
    private $matchers;

    /** @var  ErrorCodesCatalog $errorCodesCatalog */
    private $errorCodesCatalog;

    /** @var bool */
    private $debugMode;

    /** @var bool */
    private $needRenderExceptionErrorPage = true;

    /** @var string */
    private $extraMemory;

    /**
     * @var callable
     */
    private $logLastFatalError;

    /**
     * @var ResponseRenderer|null
     */
    private $responseRenderer;

    /**
     * @param LoggerInterface $logger
     * @param ErrorCodesCatalog $errorCodesCatalog
     * @param Matcher[] $matchers
     * @param callable $logLastFatalError function(array $error)
     * @param bool $debugMode
     * @param ResponseRenderer $responseRenderer
     */
    public function __construct(
        LoggerInterface $logger,
        ErrorCodesCatalog $errorCodesCatalog,
        array $matchers,
        callable $logLastFatalError,
        $debugMode,
        ResponseRenderer $responseRenderer = null
    ) {
        $matchers[] = new FilePathMatcher(__DIR__, Matcher::IGNORE);

        $this->logger = $logger;
        $this->errorCodesCatalog = $errorCodesCatalog;
        $this->matchers = $matchers;
        $this->debugMode = $debugMode;
        $this->extraMemory = str_repeat('x', 4096);
        $this->logLastFatalError = $logLastFatalError;
        $this->responseRenderer = $responseRenderer;
    }

    /**
     * @param int $errorCode
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @return false
     */
    public function handleError($errorCode, $message, $file, $line, $context = [])
    {
        $errorCode = (int)$errorCode;
        $line = (int)$line;
        $errorType = $this->errorCodesCatalog->getErrorTypeToString($errorCode);
        $trace = $this->getTrace();

        $errorEvent = new ErrorEvent($errorCode, $file, $errorType, $line, $message, $trace, $context);

        if ($this->needToHandleError($errorEvent)) {
            $this->processError($errorEvent);
        }

        return false;
    }

    public function handleException(Exception $exception)
    {
        $errorMessage = $this->createExceptionMessage($exception);
        $errorEvent = new ErrorEvent('Exception', $exception->getFile(), 'UNCAUGHT_EXCEPTION',
            $exception->getLine(), $errorMessage, $exception->getTrace(), $this->createExceptionContext($exception));

        $this->processError($errorEvent);
    }


    public function onShutdown()
    {
        $this->allocateMemory();
        $this->handleLastError();
    }


    public function stopRenderExceptionErrorPage()
    {
        $this->needRenderExceptionErrorPage = false;
    }

    private function processError(ErrorEvent $errorEvent)
    {
        $this->logger->log(
            $this->errorCodesCatalog->getLogLevel($errorEvent->getCode()),
            $errorEvent->getMessage(),
            $this->buildContextByEvent($errorEvent)
        );

        if ($this->needToDisplayErrorPage($errorEvent->getCode()) && $this->responseRenderer) {
            $this->responseRenderer->render();
        }
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    private function createExceptionMessage(\Exception $exception){
        $message = [];
        $message[] = "TYPE: " . get_class($exception);
        $message[] = "CODE: {$exception->getCode()}";
        $message[] = "MESSAGE: {$exception->getMessage()}";
        return join('; ', $message);
    }

    private function buildContextByEvent(ErrorEvent $errorEvent)
    {
        $context = [];
        $errorContext = $errorEvent->getContext();

        $context['message'] = $errorEvent->getMessage();
        $context['file']    = $errorEvent->getFile();
        $context['line']    = $errorEvent->getLine();
        $context['type']    = $errorEvent->getType();
        if ($errorEvent->getTrace()) {
            $context['trace']   = $errorEvent->getTrace();
        }

        $errorContext = $this->filterErrorContext($errorContext);

        if (!empty($errorContext)) {
            $context['errorContext'] = $errorContext;
        }

        return $context;
    }

    /**
     * @param ErrorEvent $errorEvent
     * @return bool
     */
    private function needToHandleError(ErrorEvent $errorEvent)
    {
        foreach ($this->matchers as $matcher) {
            $match = $matcher->match($errorEvent);
            if (in_array($match, [Matcher::HANDLE, Matcher::IGNORE], true)) {
                return $match === Matcher::HANDLE;
            }
        }

        return true;
    }

    private function getTrace()
    {
        //TODO: Использовать trace XDebug - xdebug_get_function_stack() (если есть), так как он информативнее
        $trace = debug_backtrace();

        return array_slice($trace, 2);
    }


    private function allocateMemory()
    {
        $this->extraMemory = '';
        gc_collect_cycles();

        // Выделяем доп. память на случай "Allowed memory size of N bytes exhausted"
        $memoryUsage = memory_get_peak_usage(true);
        $memory_50MB = 50000000;
        ini_set('memory_limit', $memoryUsage + $memory_50MB);
    }

    private function handleLastError()
    {
        $error = error_get_last();
        //Пишем последнюю фатальную ошибку. Может пригодиться, если ErrorHandler генерирует ошибку.
        $errorType = $error['type'];
        if ($this->errorCodesCatalog->isFatalError($errorType)) {
            call_user_func($this->logLastFatalError, $error);

            $errorEvent = new ErrorEvent(
                $errorType,
                $error["file"],
                $this->errorCodesCatalog->getErrorTypeToString($errorType),
                $error["line"],
                $error["message"]
            );

            if ($this->errorCodesCatalog->isFatalError($errorType) && !$this->errorCodesCatalog->isUserGeneratedError($errorType) ) {
                $this->processError($errorEvent);
            }
        }
    }

    /**
     * @param int $errorCode
     * @return bool
     */
    private function needToDisplayErrorPage($errorCode)
    {
        return
            !$this->debugMode &&
            (
                $this->errorCodesCatalog->isFatalError($errorCode) ||
                (
                    $this->errorCodesCatalog->isException($errorCode) &&
                    $this->needRenderExceptionErrorPage                )
            );
    }

    /**
     * @param mixed[] $errorContext
     * @return array
     */
    private function filterErrorContext($errorContext)
    {
        $result = [];

        foreach ($errorContext as $key => $value) {
            if ($key === 'GLOBALS') {
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param Exception $exception
     * @return array
     */
    private function createExceptionContext(Exception $exception)
    {
        $result = [];

        $rc = new \ReflectionClass($exception);

        /** @var \ReflectionProperty[] $nonEssentialProperties */
        $nonEssentialProperties = array_filter($rc->getProperties(), function (ReflectionProperty $p) {
            return $p->getDeclaringClass()->getName() !== Exception::class;
        });

        foreach ($nonEssentialProperties as $p) {
            $p->setAccessible(true);
            $result[$p->getName()] = $p->getValue($exception);
        }

        if ($exception->getPrevious()) {
            $result['previousException'] = $exception->getPrevious();
        }

        return $result;
    }
}
