<?php

namespace eLama\ErrorHandler\Bundle\EventListener;

use eLama\ErrorHandler\ErrorHandler;
use eLama\ErrorHandler\ErrorHandlerContainer;
use eLama\ErrorHandler\Exception\ErrorHandlerIsNotInitializedException;
use eLama\ErrorHandler\ResponseRenderer\WebResponseRendererFactory;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\DummyException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SymfonyExceptionHandler
{
    /**
     * @var ErrorHandler
     */
    private $errorHandler = null;

    /**
     * @param ConsoleExceptionEvent|Event|GetResponseForExceptionEvent $event
     */
    public function onException(Event $event)
    {
        $exception = $event->getException();

        if ($exception instanceof HttpException && $exception->getStatusCode() < 500) {
            return;
        }

        if ($exception instanceof DummyException || $exception instanceof AccessDeniedException) {
            return;
        }

        $this->safeCall(function () use ($exception) {
            $this->getErrorHandler()->stopRenderExceptionErrorPage();
            $this->getErrorHandler()->handleException($exception);
        });
    }

    public function stopRenderExceptionErrorPage()
    {
        $this->safeCall(function() {
            $this->getErrorHandler()->stopRenderExceptionErrorPage();
        });
    }

    /**
     * @return ErrorHandler
     */
    private function getErrorHandler()
    {
        if (!$this->errorHandler) {
            $this->errorHandler = ErrorHandlerContainer::getErrorHandler();
        }

        return $this->errorHandler;
    }

    /**
     * @param callable $f
     */
    private function safeCall(callable $f)
    {
        try {
            $f();
        } catch (ErrorHandlerIsNotInitializedException $e) {
            $renderer = WebResponseRendererFactory::createFromGlobals()->createResponseRenderer();
            $renderer->render();

            die;
        }
    }
}
