<?php

namespace eLama\ErrorHandler\Matcher;

use eLama\ErrorHandler\ErrorEvent;

class FilePathMatcher implements Matcher
{
    /**
     * @var
     */
    private $path;
    /**
     * @var bool
     */
    private $actionOnMatch;

    /**
     * @param string $path
     * @param int $actionOnMatch Одна из констант: HANDLE или IGNORE
     */
    public function __construct($path, $actionOnMatch)
    {
        $this->guardExistingDirectory($path);
        $this->path = realpath($path);
        $this->actionOnMatch = $actionOnMatch;
    }

    /**
     * {@inheritdoc}
     */
    public function match(ErrorEvent $event)
    {
        $matches = strpos($event->getFile(), $this->path) !== false;

        return $matches ? $this->actionOnMatch : self::NO_MATCH;
    }

    private function guardExistingDirectory($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('`%s` is not an existing directory', $path));
        }
    }
}
