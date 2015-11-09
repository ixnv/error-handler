<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\LogNormalizer;
use Monolog\Formatter\GelfMessageFormatter;

class GraylogFormatter extends GelfMessageFormatter
{
    /**
     * @var LogNormalizer
     */
    private $normalizer;

    /**
     * @var BacktraceConverter
     */
    private $backtraceConverter;

    /**
     * @param string $systemName
     * @param string $extraPrefix
     * @param string $contextPrefix
     */
    public function __construct($systemName = null, $extraPrefix = null, $contextPrefix = 'ctxt_')
    {
        $this->normalizer = new LogNormalizer();
        $this->backtraceConverter = new BacktraceConverter();
        parent::__construct($systemName, $extraPrefix, $contextPrefix);
    }

    /**
     * @inheritdoc
     */
    public function format(array $record)
    {
        if (isset($record['context']['trace']) && is_array($record['context']['trace'])) {
            $record['context']['trace'] = $this->backtraceConverter->convertToString($record['context']['trace']);
        }

        foreach ($record as $index => $item) {
            $record[$index] = $this->normalizer->normalize($item);
        }

        return parent::format($record);
    }

    /**
     * @inheritdoc
     */
    public function formatBatch(array $records)
    {
    }
}
