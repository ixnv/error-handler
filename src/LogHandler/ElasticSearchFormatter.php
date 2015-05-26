<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\LogNormalizer;
use eLama\ErrorHandler\LogProcessor\ContextNameProcessor;
use Monolog\Formatter\FormatterInterface;

class ElasticSearchFormatter implements FormatterInterface
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
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record = $this->convertInconsistentFieldsToJson($record);

        foreach ($record as $item) {
            $record[$item] = $this->normalizer->normalize($item);
        }

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
