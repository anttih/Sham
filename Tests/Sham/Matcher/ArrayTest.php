<?php
require_once 'Sham/Matcher/ArrayMatcher.php';
require_once 'Sham/Matcher/Any.php';

use Sham\Matcher\ArrayMatcher,
    Sham\Matcher\Any;

class Sham_Matcher_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testShouldCorrectlyMatchEmptyArray()
    {
        $matcher = new ArrayMatcher(array());
        $this->assertTrue($matcher->matches(array()));
        $this->assertFalse($matcher->matches(array(1)));
    }
    
    public function testShouldNotMatchNonArrays()
    {
        $matcher = new ArrayMatcher(array(3));
        $this->assertFalse($matcher->matches(3));
        $this->assertFalse($matcher->matches('3'));
        $this->assertFalse($matcher->matches(null));
    }
    
    public function testShouldNotMatchArraysOfDifferentLength()
    {
        $matcher = new ArrayMatcher(array(1, 2, null));
        $this->assertTrue($matcher->matches(array(1, 2, null)));
        $this->assertFalse($matcher->matches(array(1, 2)));
        $this->assertFalse($matcher->matches(array()));
        $this->assertFalse($matcher->matches(array(1, 2, null, null)));
    }
    
    public function testShouldMatchIfAllElementsStrictlyEqual()
    {
        $matcher = new ArrayMatcher(array(1, false, null));
        $this->assertTrue($matcher->matches(array(1, false, null)));
        $this->assertFalse($matcher->matches(array(1, null, null)));
        $this->assertFalse($matcher->matches(array('1', false, null)));
        $this->assertFalse($matcher->matches(array(1, false)));
    }
    
    public function testShouldNotMatchIfElementsOnlyWeaklyEqual()
    {
        $matcher = new ArrayMatcher(array(1, 2));
        $this->assertFalse($matcher->matches(array(1, '2')));
    }
    
    public function testShouldNotMatchOnKeyMismatch()
    {
        $matcher = new ArrayMatcher(array(3 => 1, 'foo' => 2));
        $this->assertTrue($matcher->matches(array(3 => 1, 'foo' => 2)));
        $this->assertTrue($matcher->matches(array('foo' => 2, 3 => 1)));
        $this->assertFalse($matcher->matches(array(2 => 1, 'foo' => 2)));
        $this->assertFalse($matcher->matches(array(3 => 1, 'boo' => 2)));
    }
    
    public function testShouldMatchRecursively()
    {
        $matcher = new ArrayMatcher(array(1, array(7), 3));
        $this->assertTrue($matcher->matches(array(1, array(7), 3)));
        $this->assertFalse($matcher->matches(array(1, array(5), 3)));
    }
    
    public function testShouldUseEmbeddedMatchers()
    {
        $matcher = new ArrayMatcher(array(1, new Any(), 3));
        $this->assertTrue($matcher->matches(array(1, 2, 3)));
        $this->assertTrue($matcher->matches(array(1, null, 3)));
        $this->assertTrue($matcher->matches(array(1, false, 3)));
        $this->assertTrue($matcher->matches(array(1, array(7), 3)));
    }
}
