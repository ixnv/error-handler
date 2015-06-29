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

    public function __construct(LogNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritdoc
     */
    public function format(array $record)
    {
        foreach ($record as $index => $item) {
            $record[$index] = $this->normalizer->normalize($item);
        }
        parent::format($record);
    }

    /**
     * @inheritdoc
     */
    public function formatBatch(array $records)
    {
    }
}
