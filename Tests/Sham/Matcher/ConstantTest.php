<?php
require_once 'Sham/Matcher/Constant.php';
class Sham_Matcher_ConstantTest extends PHPUnit_Framework_TestCase
{
    public function testShouldMatchStrictlyEqualValues()
    {
        $matcher = new Sham_Matcher_Constant(3);
        $this->assertTrue($matcher->matches(3));
    }

    public function testShouldNotMatchWeaklyEqualValues()
    {
        $matcher = new Sham_Matcher_Constant(3);
        $this->assertFalse($matcher->matches('3'));
    }

    public function testShouldNotMatchNonEqual()
    {
        $matcher = new Sham_Matcher_Constant(4);
        $this->assertFalse($matcher->matches(3));
    }
}
