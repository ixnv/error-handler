<?php

namespace eLama\ErrorHandler\Test\LogHandler;

use eLama\ErrorHandler\LogHandler\BacktraceConverter;

class BacktraceConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function format_TraceExceedsMaxElementsWithArgs_ReturnsStringWithoutArgs()
    {
        $converter = $this->createBacktraceConverter('', $maxElementsWithArgs = 0);

        $trace = $converter->convertToString($this->createIncomingMessage());

        $this->assertEquals($this->createTraceStringWithoutArgs(), $trace);
    }

    /**
    * @test
    */
    public function format_GivenTraceDataWithoutFullPath_ShouldCutNothing()
    {
        $converter = $this->createBacktraceConverter();

        $trace = $converter->convertToString($this->createIncomingMessage($rootPath = ''));

        $this->assertEquals($this->createTraceStringWithoutRootPath(), $trace);
    }

    /**
    * @test
    */
    public function format_GivenTraceDataWithFullPath_ShouldLeaveOnlyProjectRoot()
    {
        $converter = $this->createBacktraceConverter();

        $trace = $converter->convertToString($this->createIncomingMessage());

        $this->assertEquals($this->createTraceStringWithoutRootPath(), $trace);
    }

    /**
     * @param string $rootPath
     * @return array
     */
    private function createIncomingMessage($rootPath = '/var/www/')
    {
        return [
            [
                'file' => $rootPath . 'vendor/guzzlehttp/command/src/AbstractClient.php',
                'line' => 140,
                'function' => 'emit',
                'class' => 'GuzzleHttp\Event\Emitter',
                'type' => '->',
                'args' => [
                    'foo'=> 1,
                    'bar' => 2
                ],
                'object' => new TestTraceObject()
            ]
        ];
    }

    /**
     * @return string
     */
    private function createTraceStringWithoutArgs()
    {
        return '0. /var/www/vendor/guzzlehttp/command/src/AbstractClient.php:140 GuzzleHttp\Event\Emitter->emit' .
            ' Object: {"field":"test"}';
    }

    /**
     * @return string
     */
    private function createTraceStringWithoutRootPath()
    {
        return '0. vendor/guzzlehttp/command/src/AbstractClient.php:140 GuzzleHttp\Event\Emitter->emit' .
            ' Object: {"field":"test"} Args: {"foo":1,"bar":2}';
    }

    /**
     * @param string $pathToCutFromFilename
     * @param int $maxElementsWithArgs
     * @return BacktraceConverter
     */
    private function createBacktraceConverter($pathToCutFromFilename = '/var/www/', $maxElementsWithArgs = 10)
    {
        return new BacktraceConverter($pathToCutFromFilename, $maxElementsWithArgs);
    }
}

class TestTraceObject
{
    public $field = 'test';
}
