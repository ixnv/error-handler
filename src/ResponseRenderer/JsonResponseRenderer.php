<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class JsonResponseRenderer extends WebResponseRenderer
{
    /**
     * @return string
     */
    protected function getResponseBody()
    {
        return json_encode(['error' => 'Неизвестная ошибка'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
