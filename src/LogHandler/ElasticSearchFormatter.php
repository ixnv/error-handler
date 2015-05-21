<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\ContextConverter;
use eLama\ErrorHandler\LogProcessor\ContextNameProcessor;
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
        $record = $this->convertInconsistentFieldsToJson($record);

        $record['context'] = $this->contextConverter->normalize($record['context']);
        $record = $this->renameContext($record);

        return json_encode($record);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        // TODO: Implement formatBatch() method.
    }

    /**
     * @param mixed[] $record
     * @return mixed[]
     */
    private function convertInconsistentFieldsToJson(array $record)
    {
        if (array_key_exists('trace', $record['context'])) {
            $record['context']['trace'] = json_encode($record['context']['trace']);
        }

        return $record;
    }

    /**
     * @param mixed[] $record
     * @return mixed[]
     */
    private function renameContext(array $record)
    {
        $processor = new ContextNameProcessor();
        $record = $processor($record);

        return $record;
    }
}
