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
     * @test
     */
    public function normalize_ArrayExceedsMaxElementsLimit_ReturnsMaxElements()
    {
        $input = [];

        for ($i = 0; $i < LogNormalizer::MAX_ARRAY_ELEMENTS + 10; ++$i) {
            $input[] = 'element';
        }

        $result = $this->normalizer->normalize($input);

        assertThat($result, is(arrayWithSize(LogNormalizer::MAX_ARRAY_ELEMENTS + 1)));
        assertThat(end($result), is(equalTo('... 10 more')));
    }


    /**
     * @test
     */
    public function normalize_ArrayExceedsNestingLimit_ReturnsMaxElements()
    {
        $input = ['element', 'element 2'];

        $maxNesting = 2;
        for ($i = 0; $i < $maxNesting; ++$i) {
            $input = [$input, 'element'];
        }

        $result = $this->normalizer->normalize($input, $maxNesting);

        assertThat($result, is(equalTo(['array(2)', 'element'])));
    }

    /**
     * @test
     */
    public function normalize_Object_ReturnsClassAndItsFields()
    {
        $class = new AClass();

        $result = $this->normalizer->normalize($class);

        assertThat($result, is(equalTo([
            '__class_name' => 'eLama\ErrorHandler\Test\AClass',
            'a' => 'a',
            'b' => ['b']
        ])));
    }

    /**
     * @test
     */
    public function normalize_ObjectAndNestingIsExceeded_ReturnsClassName()
    {
        $class = new AClass();

        $result = $this->normalizer->normalize($class, 0);

        assertThat($result, is(equalTo('[object of class `eLama\ErrorHandler\Test\AClass`]')));
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
