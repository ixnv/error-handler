<?php

namespace eLama\ErrorHandler;

class LogNormalizer
{
    const DEFAULT_MAX_NESTING_LEVEL = 10;
    const MAX_ARRAY_ELEMENTS = 25;
    const MAX_STRING_SIZE_IN_BYTES = 32776;

    /**
     * @param mixed $record
     * @param int $nesting
     * @return mixed
     */
    public function normalize($record, $nesting = self::DEFAULT_MAX_NESTING_LEVEL)
    {
        if (is_array($record)) {
            $record = $this->normalizeArray($record, $nesting);
        } elseif (is_object($record)) {
            $record = $this->normalizeObject($record, $nesting);
        } elseif (is_resource($record)) {
            $record = sprintf('[%s of type `%s`]', (string)$record, get_resource_type($record));
        } elseif (is_string($record) && strlen($record) > self::MAX_STRING_SIZE_IN_BYTES) {
            $record = substr($record, 0, self::MAX_STRING_SIZE_IN_BYTES);
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
                $result[$key] = $this->normalize($record, --$nesting);
            }
        } else {
            $result = sprintf('array(%d)', count($records));
        }

        return $result;
    }

    /**
     * @param object $object
     * @param int $nesting
     * @return mixed
     */
    private function normalizeObject($object, $nesting)
    {
        $context = null;

        if ($nesting > 0) {
            $context = [];
            $reflection = new \ReflectionObject($object);
            $context['__class_name'] = $reflection->getName();
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $context[$property->getName()] = $this->normalize($property->getValue($object), $nesting);
            }
        } else {
            $context = sprintf('[object of class `%s`]', get_class($object));
        }

        return $context;
    }
}
