<?php

namespace eLama\ErrorHandler\Test\LogProcessor;

use eLama\ErrorHandler\LogProcessor\ContextNameProcessor;

class ContextNameProcessorTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_MESSAGE = 'A message';
    const DEFAULT_CHANNEL = 'Channel';

    private $validContext = [
        'a' => 1
    ];

    /**
     * @var ContextNameProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->processor = new ContextNameProcessor();
    }

    /**
     * @test
     */
    public function recordWithoutContext_ReturnsAsIs()
    {
        $initialArray = ['data' => 1];

        $processed = $this->processor->__invoke($initialArray);

        assertThat($processed, is(equalTo($initialArray)));
    }

    /**
     * @test
     */
    public function recordHasContextWithChannelName_PrefixesContextWithChannelName()
    {
        $record = $this->createValidRecordWithContext($this->validContext);

        $processed = $this->processor->__invoke($record);

        assertThat($processed, hasKeyValuePair(
            matchesPattern(sprintf('/^%s_context/i', self::DEFAULT_CHANNEL)),
            equalTo($this->validContext)
        ));
    }

    /**
     * @test
     */
    public function recordHasContextWithEmptyChannelName_ChannelNameIsMessageHash()
    {
        $record = $this->createValidRecordWithContext($this->validContext);
        $record['channel'] = '';

        $processed = $this->processor->__invoke($record);

        assertThat($processed, hasKeyValuePair(
            matchesPattern(sprintf('/^%s_context/i', '5a8231c7d84ce51e0aace1792c9b4e51')),
            equalTo($this->validContext)
        ));
    }

    /**
     * @test
     */
    public function recordHasContext_RemovesContextFromArray()
    {
        $record = $this->createValidRecordWithContext($this->validContext);

        $processed = $this->processor->__invoke($record);

        assertThat($processed, not(hasKey('context')));
    }

    /**
     * @return mixed[]
     */
    private function createValidRecord()
    {
        return [
            'message' => self::DEFAULT_MESSAGE,
            'channel' => self::DEFAULT_CHANNEL
        ];
    }

    /**
     * @param mixed[] $context
     * @return mixed[]
     */
    private function createValidRecordWithContext(array $context)
    {
        $record = $this->createValidRecord();
        $record['context'] = $context;

        return $record;
    }
}
