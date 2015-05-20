<?php

namespace eLama\ErrorHandler\LogProcessor;

/**
 * Should be last in chain (First in Monolog Processors)
 */
class ContextNameProcessor
{
    public function __invoke(array $record)
    {
        if (!array_key_exists('context', $record)) {
            return $record;
        }

        $channelName = (empty($record['channel'])) ? md5($record['message']) : $record['channel'];

        $record[mb_strtolower($channelName) . '_context'] = $record['context'];
        unset($record['context']);

        return $record;
    }
}
