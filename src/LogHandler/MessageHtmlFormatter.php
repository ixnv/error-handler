<?php

namespace eLama\ErrorHandler\LogHandler;

use Monolog\Formatter\NormalizerFormatter;

class MessageHtmlFormatter extends NormalizerFormatter
{
    /**
     * Formats a log record.
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $message   = $record['message'];
        $file      = $record['context']['file'];
        $line      = $record['context']['line'];
        $errorType = $record['context']['type'];
        if (isset($record['context']['trace'])) {
            $trace     = $record['context']['trace'];

            $traceFormatted = $this->formatTraceHtml($trace);
        } else {
            $traceFormatted = '<i>NONE</i>';
        }
        $output
            = <<<HTML
<div style="display: inline-block; border: 1px solid red;">
    <h5 style="background: red; color: white; margin: 0 0 1em 0;">{$errorType} in {$file}:{$line}</h5>
    <code><pre>$message</pre></code>
    <h6 style="margin: 0;">Trace</h6>
    {$traceFormatted}
</div>
HTML;

        return $output;
    }

    private function formatTraceHtml($trace)
    {


        $rows = [];
        foreach ($trace as $index => $traceItem) {
            $traceItem = array_merge(['file' => '', 'line' => ''], $traceItem);
            $functionName = (isset($traceItem['class']) ? ($traceItem['class'] . $traceItem['type']) : '') .
                $traceItem['function'];
            $functionArgs = array_map(function ($item) {
                $varType = (gettype($item) == 'object') ? get_class($item) : gettype($item);
                $varValue = $this->getVarValue($item);

                return "$varType $varValue";
            }, $traceItem['args']);
            $functionArgs = join(', ', $functionArgs);
            $row
                = <<<HTML
<tr>
    <td>{$index}</td>
    <td>{$traceItem['file']}:{$traceItem['line']}</td>
    <td>{$functionName}($functionArgs)</td>
</tr>
HTML;
            $rows[] = $row;
        }
        $tableBody = join("\n", $rows);
        $output
            = <<<HTML
<table style="
    border: 1px solid black;
    border-collapse: separate;
">
    <thead>
    <tr>
        <th>#</th><th>File(Line)</th><th>Function</th>
    </tr>
    </thead>
    <tbody>
        {$tableBody}
    </tbody>
</table>
HTML;

        return $output;
    }

    /**
     * Formats a set of log records.
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        // TODO: Implement formatBatch() method.
    }

    /**
     * @param $item
     * @return string
     */
    private function getVarValue($item)
    {
        $result = null;
        switch (gettype($item)) {
            case 'object':
                $result = '[object]';
                break;
            case 'array':
                $result = '[array(' . count($item) . ')]';
                break;
            case 'NULL':
            case 'boolean':
                $result = var_export($item, true);
                break;
            default:
                $result = (string)$item;
                break;
        }

        return $result;
    }
}
