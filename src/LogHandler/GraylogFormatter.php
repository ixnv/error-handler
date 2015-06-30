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

    public function __construct($systemName = null, $extraPrefix = null, $contextPrefix = 'ctxt_')
    {
        $this->normalizer = new LogNormalizer();
        parent::__construct($systemName, $extraPrefix, $contextPrefix);
    }

    /**
     * @inheritdoc
     */
    public function format(array $record)
    {
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
