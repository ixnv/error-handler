<?php

namespace eLama\ErrorHandler;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class FoldingPageCreator
{
    /**
     * @return string
     */
    public function getErrorUserText()
    {
        ob_start();
        require __DIR__ . '/error_page.php';

        return ob_get_clean();
    }

    public function clearOutputBuffer()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }

    /**
     * @param ConsoleExceptionEvent|GetResponseForExceptionEvent $event
     */
    public function prepareResponseForEvent(Event $event)
    {
        $this->clearOutputBuffer();
        if (!$event->getResponse()) {
            $event->setResponse(new Response());
        }

        $response = $event->getResponse();
        $response->setStatusCode(500);
        $response->setContent($this->getErrorUserText());
    }

    public function buildMessageByException(\Exception $exception)
    {
        $message = [];
        $message[] = "TYPE: " . get_class($exception);
        $message[] = "CODE: {$exception->getCode()}";
        $message[] = "MESSAGE: {$exception->getMessage()}";
        return join('; ', $message);
    }

    public function sendResponseCode500()
    {
        if (function_exists('http_response_code')) {
            \http_response_code(500);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }
    }
}
