<?php
require_once 'Mocker/Call.php';
class Mocker_CallTest extends PHPUnit_Framework_TestCase
{

    public function testShouldReturnReturnValue()
    {
        $call = new Mocker_Call('call name', array(), 'return value');
        $this->assertEquals('return value', $call());
    }

    public function testShouldBeAbleToSetReturnValue()
    {
        $call = new Mocker_Call('call name', array());
        $call->returns('return value');
        $this->assertEquals('return value', $call());
    }

    /**
     * @expectedException Exception
     */
    public function testShouldThrowIfSet()
    {
        $call = new Mocker_Call('call name', array(), 'return value');
        $call->throws('Exception');
        $call();
    }
}
