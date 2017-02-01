<?php

namespace eLama\ErrorHandler\ResponseRenderer;

class WebResponseRendererFactory
{
    /**
     * @var string
     */
    private $acceptHeader;
    /**
     * @var string
     */
    private $contentTypeHeader;
    /**
     * @var string
     */
    private $input;

    /**
     * @param string $rendererType
     * @return ResponseRenderer
     */
    public static function createRenderer($rendererType)
    {
        switch ($rendererType) {
            case 'json':
                $responseRenderer = new JsonResponseRenderer();
                break;
            case 'soap11':
                $responseRenderer = new Soap11ResponseRenderer();
                break;
            case 'soap12':
                $responseRenderer = new Soap12ResponseRenderer();
                break;
            case 'html':
                $responseRenderer = new HtmlResponseRenderer();
                break;
            case 'plain':
                $responseRenderer = new PlainTextResponseRenderer();
                break;
            case 'auto':
            default:
                $responseRenderer = self::createFromGlobals()->createResponseRenderer();
        }

        return $responseRenderer;
    }

    private static function createFromGlobals()
    {
        $inputPart = '';
        if (strtoupper(self::get($_SERVER, 'REQUEST_METHOD', '')) === 'POST') {
            $inputHandler = fopen('php://input', 'r');
            $inputPart = fread($inputHandler, 512);
            fclose($inputHandler);
        }

        return new self(
            self::get($_SERVER, 'HTTP_ACCEPT', ''),
            self::get($_SERVER, 'CONTENT_TYPE', ''),
            $inputPart
        );
    }

    /**
     * @param string $acceptHeader
     * @param string $contentTypeHeader
     * @param string $requestBody
     */
    public function __construct($acceptHeader, $contentTypeHeader, $requestBody)
    {
        $this->acceptHeader = $acceptHeader;
        $this->contentTypeHeader = $contentTypeHeader;
        $this->input = $requestBody;
    }

    public function createResponseRenderer()
    {
        $responseRenderer = new PlainTextResponseRenderer();
        if ($this->accepts('text/html') || $this->accepts('application/xhtml')) {
            $responseRenderer = new HtmlResponseRenderer();
        } elseif ($this->accepts('application/json')) {
            $responseRenderer = new JsonResponseRenderer();
        } elseif ($this->accepts('application/soap+xml') || $this->contentTypeIs('application/soap+xml')) {
            $responseRenderer = new Soap12ResponseRenderer();
        } elseif (($this->accepts('text/xml') || $this->contentTypeIs('text/xml'))
            && $this->requestBodyContains('http://schemas.xmlsoap.org/soap/envelope')
        ) {
            $responseRenderer = new Soap11ResponseRenderer();
        }

        return $responseRenderer;
    }

    private function accepts($mimeType)
    {
        return strpos($this->acceptHeader, $mimeType) !== false;
    }

    private function contentTypeIs($mimeType)
    {
        return strpos($this->contentTypeHeader, $mimeType) !== false;
    }

    private function requestBodyContains($string)
    {
        return strpos($this->input, $string) !== false;
    }

    /**
     * @param array|\ArrayAccess $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
