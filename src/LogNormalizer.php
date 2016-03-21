<?php

namespace eLama\ErrorHandler;

class LogNormalizer
{
    const DEFAULT_MAX_NESTING_LEVEL = 10;
    const MAX_ARRAY_ELEMENTS = 20;
    const MAX_STRING_SIZE_IN_BYTES = 32766;
    const LAST_LINE_END = '...';

    /**
     * @param mixed $record
     * @param int $nesting
     * @return mixed
     */
    public function normalize($record, $nesting = self::DEFAULT_MAX_NESTING_LEVEL)
    {
        if (is_resource($record)) {
            $record = sprintf('[%s of type `%s`]', (string)$record, get_resource_type($record));
        } elseif (is_string($record) && strlen($record) > self::MAX_STRING_SIZE_IN_BYTES) {
            $record = substr($record, 0, self::MAX_STRING_SIZE_IN_BYTES - strlen(self::LAST_LINE_END)) .
                self::LAST_LINE_END;
        }

        return $record;
    }

    /**
     * @param array $records
     * @param int $nesting
     * @return array
     */
    private function normalizeArray(array $records, $nesting)
    {
        $result = [];

        if ($nesting > 0) {
            $initialElementCount = count($records);

            if ($initialElementCount > self::MAX_ARRAY_ELEMENTS) {
                $records = array_slice($records, 0, self::MAX_ARRAY_ELEMENTS, true);
                $records[] = sprintf('... %d more', $initialElementCount - self::MAX_ARRAY_ELEMENTS);
            }

            foreach ($records as $key => $record) {
                $result[$key] = $this->normalize($record, $nesting);
            }
        } else {
            $result = sprintf('array(%d)', count($records));
        }

        return $result;
    }
}
