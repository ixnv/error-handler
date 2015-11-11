<?php

namespace eLama\ErrorHandler\LogHandler;

class BacktraceConverter
{
    /**
     * @var int
     */
    private $maxElementsWithArgs;

    /**
     * @var string
     */
    private $pathToCutFromFilename;

    /**
     * @param string $pathToCutFromFilename
     * @param int $maxElementsWithArgs
     */
    public function __construct($pathToCutFromFilename = '/var/www/', $maxElementsWithArgs = 10)
    {
        $this->pathToCutFromFilename = $pathToCutFromFilename;
        $this->maxElementsWithArgs = $maxElementsWithArgs;
    }

    /**
     * @param array $backtrace
     * @return string
     */
    public function convertToString(array $backtrace)
    {
        $result = [];

        foreach ($backtrace as $i => $item) {
            $result[] = $i . '. ' . $this->formatTraceString($item, $this->shouldLeaveArgsInTrace($i));
        }

        return implode($result, "\n\n");
    }

    /**
     * @param array $traceItem
     * @param bool $withArguments
     * @return string
     */
    private function formatTraceString(array $traceItem, $withArguments = false)
    {
        return trim((isset($traceItem['file']) ? '' . $this->formattedFilename($traceItem['file']) . '' : '') .
            (isset($traceItem['line']) ? ':' . $traceItem['line'] . ' ' : '') .
            (isset($traceItem['class']) ? '' . $traceItem['class'] . '' : '') .
            (isset($traceItem['type']) ? '' . $traceItem['type'] . '' : '') .
            (isset($traceItem['function']) ? '' . $traceItem['function'] . ' ' : '') .
            (isset($traceItem['object']) ? 'Object: ' . $this->toJson($traceItem['object']) . ' ' : '') .
            ((isset($traceItem['args']) && $withArguments) ? 'Args: ' . $this->toJson($traceItem['args']) . ' ' : ''));
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function toJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $filename
     * @return string
     */
    private function formattedFilename($filename)
    {
        if (!empty($this->pathToCutFromFilename) && strpos($filename, $this->pathToCutFromFilename) === 0) {
            return substr($filename, strlen($this->pathToCutFromFilename));
        }

        return $filename;
    }

    /**
     * @param int $currentElement
     * @return bool
     */
    private function shouldLeaveArgsInTrace($currentElement)
    {
        return ($currentElement <= $this->maxElementsWithArgs - 1);
    }
}
