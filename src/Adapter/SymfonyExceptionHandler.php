<?php

namespace eLama\ErrorHandler\Adapter;

use eLama\ErrorHandler\ErrorHandler;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\DummyException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @deprecated
 * @see eLama\ErrorHandler\Bundle\EventListener\SymfonyExceptionHandler
 */
class SymfonyExceptionHandler
{
    /** @var ErrorHandler */
    private $errorHandler;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param ConsoleExceptionEvent|GetResponseForExceptionEvent $event
     */
    public function onException(Event $event)
    {
        $exception = $event->getException();
        if ($exception instanceof HttpException && $exception->getStatusCode() < 500) {
            return;
        }

        if (!($exception instanceof DummyException)) { // warning При дебаге
            $this->errorHandler->stopRenderExceptionErrorPage();
            $this->errorHandler->handleException($exception);
        }
    }

    public function stopRenderExceptionErrorPage()
    {
        $this->errorHandler->stopRenderExceptionErrorPage();
    }
}
