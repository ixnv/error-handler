<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class PlainTextResponseRenderer extends WebResponseRenderer
{
    /**
     * @return string
     */
    protected function getResponseBody()
    {
        return 'Unknown error.' . PHP_EOL . 'Something unexpected happened, we`ll fix it as soon as possible. Sorry for inconvenience.';
    }
}
