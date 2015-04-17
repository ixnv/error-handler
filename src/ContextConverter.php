<?php

namespace eLama\ErrorHandler;

class ContextConverter
{
    const MAX_ARRAY_ELEMENTS = 25;

    /**
     * @param $context
     * @param int $nesting
     * @return array
     */
    public function normalize($context, $nesting = 5)
    {
        $nesting--;
        if(is_object($context)) {
            return $this->normalizeObject($context, $nesting);
        } elseif (is_array($context)) {
            return $this->normalizeArray($context, $nesting);
        } elseif (is_resource($context)) {
            return $this->normalizeResource($context);
        } else {
            return $context;
        }
    }

    /**
     * @param $object
     * @param int $nesting
     * @return array
     */
    protected function normalizeObject($object, $nesting)
    {
        if ($nesting >= 0) {
            $convertedContext = [];
            $reflection = new \ReflectionObject($object);
            $convertedContext['__class_name'] = $reflection->getName();
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $convertedContext[$property->getName()] = $this->normalize($property->getValue($object), $nesting);
            }
        } else {
            $convertedContext = sprintf('[object of class `%s`]', get_class($object));
        }

        return $convertedContext;
    }

    /**
     * @param $resource
     * @return array
     */
    protected function normalizeResource($resource)
    {
        return sprintf('[%s of type `%s`]', (string)$resource, get_resource_type($resource));
    }

    private function isTraversable($context)
    {
        return is_array($context) || is_object($context);
    }

    /**
     * @param $context
     * @param $nesting
     * @return string
     */
    private function normalizeArray($context, $nesting)
    {
        if ($nesting >= 0) {
            $result = [];
            if (count($context) > self::MAX_ARRAY_ELEMENTS) {
                $context = array_slice($context, 0, self::MAX_ARRAY_ELEMENTS, true);
                $context[] = '...';
            }

            foreach ($context as $contextKey => $contextItem) {
                $result[$contextKey] = $this->normalize($contextItem, $nesting);
            }

            return $result;
        } else {
            return sprintf('[array(%d)]', count($context));
        }
    }
}
