<?php

namespace eLama\ErrorHandler\ResponseRenderer;

abstract class WebResponseRenderer implements ResponseRenderer
{
    /**
     * @return void
     */
    public function render()
    {
        $this->sendResponseCode500();
        $this->clearOutputBuffer();
        echo $this->getResponseBody();
    }

    /**
     * @return string
     */
    abstract protected function getResponseBody();

    private function sendResponseCode500()
    {
        if (function_exists('http_response_code')) {
            \http_response_code(500);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }
    }

    private function clearOutputBuffer()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }
}
