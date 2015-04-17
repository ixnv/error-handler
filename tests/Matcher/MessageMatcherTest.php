<?php

namespace eLama\ErrorHandler\Test\Matcher;

use eLama\ErrorHandler\ErrorEvent;
use eLama\ErrorHandler\Matcher\Matcher;
use eLama\ErrorHandler\Matcher\MessageMatcher;
use PHPUnit_Framework_TestCase;

class MessageMatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider dataProvider_Actions
     * @param $action
     */
    public function matches_MatchingString_DoesTheAction($action)
    {
        $messageMatcher = new MessageMatcher('test message', $action);

        $result = $messageMatcher->match(new ErrorEvent(0, '', '', '', 'This is some test message'));

        $this->assertEquals($action, $result);
    }

    /**
     * @test
     * @dataProvider dataProvider_Actions
     * @param $action
     */
    public function matches_NonmatchingString_NoMatch($action)
    {
        $messageMatcher = new MessageMatcher('test message', $action);

        $result = $messageMatcher->match(new ErrorEvent(0, '', '', '', 'some other message'));

        $this->assertEquals(Matcher::NO_MATCH, $result);
    }

    public function dataProvider_Actions()
    {
        return [
            [Matcher::IGNORE],
            [Matcher::HANDLE],
        ];
    }
}
