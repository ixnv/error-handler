<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\ContextConverter;
use Monolog\Formatter\FormatterInterface;

class ElasticSearchFormatter implements FormatterInterface
{
    /**
     * @var ContextConverter
     */
    private $contextConverter;

    public function __construct(ContextConverter $contextConverter)
    {
        $this->contextConverter = $contextConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record['context'] = $this->contextConverter->normalize($record['context']);

        return $record;
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        // TODO: Implement formatBatch() method.
    }
}
