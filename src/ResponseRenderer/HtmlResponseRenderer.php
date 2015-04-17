<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class HtmlResponseRenderer extends WebResponseRenderer
{

    /**
     * @return string
     */
    protected function getResponseBody()
    {
        ob_start();
        require __DIR__ . '/error_page.php';

        return ob_get_clean();
    }
}
