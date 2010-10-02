<?php
require_once 'Sham/Matcher/Constant.php';

use Sham\Matcher\Constant;

class Sham_Matcher_ConstantTest extends PHPUnit_Framework_TestCase
{
    public function testShouldMatchStrictlyEqualValues()
    {
        $matcher = new Constant(3);
        $this->assertTrue($matcher->matches(3));
    }

    public function testShouldNotMatchWeaklyEqualValues()
    {
        $matcher = new Constant(3);
        $this->assertFalse($matcher->matches('3'));
    }

    public function testShouldNotMatchNonEqual()
    {
        $matcher = new Constant(4);
        $this->assertFalse($matcher->matches(3));
    }
}
