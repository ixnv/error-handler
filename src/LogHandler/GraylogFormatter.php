<?php

namespace eLama\ErrorHandler\LogHandler;

use Monolog\Formatter\GelfMessageFormatter;

class GraylogFormatter extends GelfMessageFormatter
{
    const MAX_STRING_SIZE_IN_BYTES = 32766;
    const LAST_LINE_END = '...';

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

        $message = parent::format($record);
        foreach ($message->getAllAdditionals() as $index => $item) {
            if (is_string($item) && strlen($item) > self::MAX_STRING_SIZE_IN_BYTES) {
                $message->setAdditional(
                    $index,
                    substr($item, 0, self::MAX_STRING_SIZE_IN_BYTES - strlen(self::LAST_LINE_END)) . self::LAST_LINE_END
                );
            }
        }


        return $message;
    }

    /**
     * @inheritdoc
     */
    public function formatBatch(array $records)
    {
    }
}
