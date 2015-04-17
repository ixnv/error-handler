<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\ContextConverter;
use Monolog\Handler\SwiftMailerHandler;
use Swift_Attachment;
use Swift_Message;

class SwiftMailerErrorHandler extends SwiftMailerHandler
{
    const MAX_TRACE_ITEMS = 5;

    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records)
    {
        $record = $records[0];
        $message = $this->createMessageFromTemplate();
        $coordinates = $this->shortenErrorCoordinates(@$record['context']['file'], @$record['context']['line']);

        $subject = $this->message->getSubject() . ': ' . $record['context']['type'] . ' in ' . $coordinates . ' # ' .  trim($record['message']);
        $subject = mb_substr($subject, 0, 1500, 'utf-8');

        $message->setSubject($subject);

        if (isset($record['context']['trace'])) {
            $this->attachJson($message, 'full-trace', $record['context']['trace']);
            $record['context']['trace'] = $this->shortenTrace($record['context']['trace']);
        }

        if (isset($record['context']['errorContext'])) {
            $this->attachJson($message, 'errorContext', $record['context']['errorContext']);
            unset($record['context']['errorContext']);
        }

        $message->setBody($this->getFormatter()->format($record));

        $this->mailer->send($message);
    }

    /**
     * @return Swift_Message
     */
    protected function createMessageFromTemplate()
    {
        return unserialize(serialize($this->message));
    }

    /**
     * @param Swift_Message $message
     * @param string $name
     * @param $jsonEncodableData
     */
    private function attachJson(Swift_Message $message, $name, $jsonEncodableData)
    {
        $fileName = $name . '.json';
        $attachment = Swift_Attachment::newInstance();

        $contextConverter = new ContextConverter();

        $jsonEncodableData = $contextConverter->normalize($jsonEncodableData, 4);

        $jsonData = $this->serializeToJson($jsonEncodableData);
        if (function_exists('bzcompress')) {
            $attachment->setContentType('application/x-bzip2');
            $attachment->setBody(bzcompress($jsonData));
            $attachment->setFilename($fileName . '.bz2');
        } else {
            $attachment->setContentType('text/plain');
            $attachment->setBody($jsonData);
            $attachment->setFilename($fileName);
        }

        $message->attach($attachment);
    }

    /**
     * @param string $file
     * @return string
     */
    private function shortenErrorCoordinates($file, $line)
    {
        if (!$line) {
            return '`somewhere...`';
        }

        $quotedDirSeparator = preg_quote(DIRECTORY_SEPARATOR, '#');

        return preg_replace('#.*((' . $quotedDirSeparator .'.*){3})$#', '...$1', $file) . ':' . $line;
    }

    /**
     * @param $trace
     * @return array
     */
    private function shortenTrace($trace)
    {
        $result = [];
        $tracePart = array_slice($trace, 0, self::MAX_TRACE_ITEMS);
        foreach ($tracePart as $index => $item) {
            $item = array_merge(['file' => '', 'line' => ''], $item);
            $method = isset($item['class']) ? $item['class'] . $item['type'] . $item['function'] : $item['function'];
            $coordinates = $this->shortenErrorCoordinates(@$item['file'], @$item['line']);

            $result[] = sprintf('#%s %s - %s()', $index, $coordinates, $method);
        }
        if (count($trace) > count($tracePart)) {
            $result[] = '...';
        }

        return $result;
    }

    /**
     * @param mixed $jsonEncodableData
     * @return string
     */
    private function serializeToJson($jsonEncodableData)
    {
        return method_exists($this->getFormatter(), 'stringify') ?
            $this->getFormatter()->stringify($jsonEncodableData)
            : json_encode($jsonEncodableData, JSON_PRETTY_PRINT);
    }
}
