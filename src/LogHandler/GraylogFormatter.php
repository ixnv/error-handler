<?php

namespace eLama\ErrorHandler\LogHandler;

use Gelf\Message;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\GelfMessageFormatter;

class GraylogFormatter implements FormatterInterface
{
    const MAX_STRING_SIZE_IN_BYTES = 32766;

    private $gelfMessageFormatter;

    /**
     * @var BacktraceConverter
     */
    private $backtraceConverter;

    /**
     * @var Source
     */
    private $source;

    /**
     * GraylogFormatter constructor.
     *
     * @param GelfMessageFormatter $messageFormatter
     * @param Source $source
     */
    public function __construct(GelfMessageFormatter $messageFormatter, Source $source = null)
    {
        $this->backtraceConverter = new BacktraceConverter();
        $this->gelfMessageFormatter = $messageFormatter;
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function format(array $record)
    {
        if (isset($record['context']['trace']) && is_array($record['context']['trace'])) {
            $record['context']['trace'] = $this->backtraceConverter->convertToString($record['context']['trace']);
        }

        $message = $this->gelfMessageFormatter->format($record);
        $this->enrichMessageWithSourceData($message);

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function formatBatch(array $records)
    {
    }

    private function enrichMessageWithSourceData(Message $message)
    {
        if (is_null($this->source)) {
            return;
        }

        $message->setHost($this->source->getSource());
        $message->setAdditional('environment', $this->source->getEnvironment());
    }
}
