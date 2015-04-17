<?php

namespace eLama\ErrorHandler\Test\Matcher;

use eLama\ErrorHandler\ErrorEvent;
use eLama\ErrorHandler\Matcher\FilePathMatcher;
use eLama\ErrorHandler\Matcher\Matcher;
use PHPUnit_Framework_TestCase;

class FilePathMatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructor_NonExistentMatchingDirectory_ThrowsAnException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'something';
        new FilePathMatcher($directory, Matcher::HANDLE);
    }

    /**
     * @test
     */
    public function match_ValidMatchingDirectory_MatchesDirectory()
    {
        $directoryMatcher = $this->createMatcher(__DIR__);

        $result = $directoryMatcher->match(new ErrorEvent(0, __FILE__, '', 0, ''));
        $this->assertSame(Matcher::HANDLE, $result);
    }

    /**
     * @test
     */
    public function match_MatchingDirectoryWithDoubleDot_ShouldHandle()
    {
        $directoryMatcher = $this->createMatcher(__DIR__ . DIRECTORY_SEPARATOR . '..');

        $this->assertSame(Matcher::HANDLE,$directoryMatcher->match(new ErrorEvent(0, __FILE__, '', 0, '')));
    }

    /**
     * @test
     */
    public function match_HandleFlagDisabled_ShouldIgnore()
    {
        $directoryMatcher = $this->createMatcher(__DIR__, Matcher::IGNORE);

        $this->assertSame(Matcher::IGNORE, $directoryMatcher->match(new ErrorEvent(0, __FILE__ , '', 0, '')));
    }

    /**
     * @test
     */
    public function match_NonMatchingFile_ShouldNotMatch()
    {
        $directoryMatcher = $this->createMatcher(__DIR__, false);

        $this->assertSame(Matcher::NO_MATCH, $directoryMatcher->match(new ErrorEvent(0, realpath(__DIR__ .'/../..') , '', 0, '')));
    }

    /**
     * @param $directory
     * @param int $actionOnMatch
     * @return FilePathMatcher
     */
    private function createMatcher($directory, $actionOnMatch = Matcher::HANDLE)
    {
        return new FilePathMatcher($directory, $actionOnMatch);
    }
}
