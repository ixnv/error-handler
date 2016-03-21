<?php

namespace eLama\ErrorHandler\Test;

use eLama\ErrorHandler\LogNormalizer;

class LogNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        $this->normalizer = new LogNormalizer();
    }

    /**
     * @test
     */
    public function normalize_InputDoesNotNeedNormalization_ReturnsInput()
    {
        assertThat($this->normalizer->normalize('a'), is(identicalTo('a')));
        assertThat($this->normalizer->normalize(1), is(identicalTo(1)));
        assertThat($this->normalizer->normalize(['a']), is(identicalTo(['a'])));
    }

    /**
     * @test
     */
    public function normalize_StringExceedsByteLimit_ReturnsTrimmedString()
    {
        $input = $this->newString(LogNormalizer::MAX_STRING_SIZE_IN_BYTES + 1);

        $result = $this->normalizer->normalize($input);

        assertThat(strlen($result), is(equalTo(LogNormalizer::MAX_STRING_SIZE_IN_BYTES)));
    }

    /**
     * @param int $bytes
     * @return string
     */
    private function newString($bytes)
    {
        return str_repeat('a', $bytes);
    }
}

class AClass
{
    private $a = 'a';
    private $b = ['b'];
}
