<?php
require_once 'Sham/Call.php';
require_once 'Sham/Exception.php';
class Sham_CallTest extends PHPUnit_Framework_TestCase
{
    public function testShouldHoldItsParameters()
    {
        $call = new Sham_Call('foo', array(1,2,3), 'xoo');
        $this->assertEquals('foo', $call->name);
        $this->assertEquals(array(1,2,3), $call->params);
        $this->assertEquals('xoo', $call->return_value);
    }
}
