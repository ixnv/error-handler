<?php

namespace eLama\ErrorHandler\Test\Matcher;

use eLama\ErrorHandler\ErrorEvent;
use eLama\ErrorHandler\Matcher\Matcher;
use eLama\ErrorHandler\Matcher\CodeMessageMatcher;
use PHPUnit_Framework_TestCase;

class CodeMessageMatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider dataProvider_Actions
     * @param $action
     */
    public function matches_MatchingString_DoesTheAction($action)
    {
        $messageMatcher = new CodeMessageMatcher(E_WARNING, 'test message', $action);

        $result = $messageMatcher->match(new ErrorEvent(E_WARNING, '', '', '', 'This is some test message'));

        $this->assertEquals($action, $result);
    }

    /**
     * @test
     * @dataProvider dataProvider_Actions
     * @param $action
     */
    public function matches_NonmatchingString_NoMatch($action)
    {
        $messageMatcher = new CodeMessageMatcher(E_WARNING, 'test message', $action);

        $result = $messageMatcher->match(new ErrorEvent(E_WARNING, '', '', '', 'some other message'));

        $this->assertEquals(Matcher::NO_MATCH, $result);
    }

    /**
     * @test
     * @dataProvider dataProvider_Actions
     */
    public function matches_MatchingStringButtCodeIsDifferent_NoMatch($action)
    {
        $messageMatcher = new CodeMessageMatcher(E_WARNING, 'test message', $action);

        $result = $messageMatcher->match(new ErrorEvent(E_USER_WARNING, '', '', '', 'This is some test message'));

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
