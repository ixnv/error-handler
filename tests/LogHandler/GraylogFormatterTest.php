<?php

namespace eLama\ErrorHandler\Test\LogHandler;

use eLama\ErrorHandler\LogHandler\GraylogFormatter;
use eLama\ErrorHandler\LogHandler\Source;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Logger;

class GraylogFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GraylogFormatter
     */
    private $graylogFormatter;

    protected function setUp()
    {
        $this->graylogFormatter = new GraylogFormatter(new GelfMessageFormatter());
    }

    /**
     * @test
     */
    public function format_GivenRecordWithBacktrace_FormatsIt()
    {
        $record = $this->createDefaultRecord();
        $record['context']['trace'] = debug_backtrace();

        $message = $this->graylogFormatter->format($record);

        assertThat(
            $message->getAdditional('ctxt_trace'),
            matchesPattern(sprintf("/^0\.\s%s.*/ui", str_replace('\\', '\\\\', __CLASS__)))
        );
    }

    /**
     * @test
     */
    public function format_GivenRecord_FormatsItSimilarToGelfFormatter()
    {
        $message = $this->graylogFormatter->format($record = $this->createDefaultRecord());

        assertThat(
            $message,
            is(equalTo((new GelfMessageFormatter())->format($record)))
        );
    }

    /**
     * @test
     */
    public function format_GivenRecordWithTooLongField_CutsItAccordingToLimit()
    {
        //формально, этот тест не нужен, оставлен, чтобы гарантировать, что код, который я удалил можно было удалять
        $record = $this->createDefaultRecord();
        $record['context']['i_am_long'] = $this->generateStringWithLength(
            GraylogFormatter::MAX_STRING_SIZE_IN_BYTES + 10
        );

        $message = $this->graylogFormatter->format($record);

        assertThat(
            strlen($message->getAdditional('ctxt_i_am_long')),
            is(lessThanOrEqualTo(GraylogFormatter::MAX_STRING_SIZE_IN_BYTES))
        );
    }

    /**
     * @test
     */
    public function format_GivenSource_SubstitutesHostValueAndAddsEnvironmentValue()
    {
        $this->graylogFormatter = new GraylogFormatter(
            new GelfMessageFormatter(),
            new Source(
                $source = 'stage',
                $environment = 'dev'
            )
        );

        $message = $this->graylogFormatter->format($this->createDefaultRecord());

        assertThat($message->toArray(), hasKeyValuePair('host', $source));
        assertThat($message->toArray(), hasKeyValuePair('_environment', $environment));
    }

    private function createDefaultRecord()
    {
        return [
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'moo',
            'context' => ['foo' => 'bar'],
            'datetime' => new \DateTime('2016-01-01 00:00:00'),
            'extra' => [],
            'message' => 'a message',
        ];
    }

    private function generateStringWithLength($length)
    {
        $str = 'a';

        return str_pad($str, $length, $str);
    }
}
