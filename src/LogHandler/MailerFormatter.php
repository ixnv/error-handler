<?php

namespace eLama\ErrorHandler\LogHandler;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;

class MailerFormatter extends LineFormatter
{
    const FORMAT = "[%datetime%] %channel%.%level_name%: %message%

=== ERROR INFO ===
%context%

=== EXTRA ===
%extra%\n";

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $this->allowInlineLineBreaks = true;
        $vars = NormalizerFormatter::format($record);

        $output = self::FORMAT;

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }

        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $output = str_replace('%context%', '', $output);
            }

            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $output = str_replace('%extra%', '', $output);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertToString($data)
    {
        if ($data instanceof \Closure) {
            return var_export($data, true);
        }

        return parent::convertToString($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        // suppress json_encode errors since it's twitchy with some inputs
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        $result = $ignoreErrors ? @json_encode($data, $flags) : json_encode($data, $flags);

        return $result;
    }
}
