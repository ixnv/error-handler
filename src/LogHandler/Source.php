<?php

namespace eLama\ErrorHandler\LogHandler;

final class Source
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $source
     * @param string $environment
     */
    public function __construct($source, $environment)
    {
        $this->source = $source;
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
