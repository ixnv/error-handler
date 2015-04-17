<?php

namespace eLama\ErrorHandler;

class ErrorEvent
{
    private $type;
    private $code;
    private $message;
    private $file;
    private $line;
    private $trace;
    private $context;

    public function __construct($code, $file, $type, $line, $message, $trace = null, $context = [])
    {
        $this->code = $code;
        $this->context = $context;
        $this->setFileAndLine($file, $line);
        $this->message = $message;
        $this->trace = $trace;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    private function setFileAndLine($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }
}
