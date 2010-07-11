<?php
require_once 'Sham/Call.php';
require_once 'Sham/Exception.php';
class Sham_CallTest extends PHPUnit_Framework_TestCase
{

    public function testShouldReturnReturnValue()
    {
        $call = new Sham_Call('call name', array(), 'return value');
        $this->assertEquals('return value', $call());
    }

    public function testShouldBeAbleToSetReturnValue()
    {
        $call = new Sham_Call('call name', array());
        $call->returns('return value');
        $this->assertEquals('return value', $call());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $call = new Sham_Call('call name', array(), false);
        $this->assertEquals(false, $call());
    }

    public function testReturnsShouldGetReturnValueWhenNoParam()
    {
        $call = new Sham_Call('call name', array(), 'return value');
        $this->assertEquals('return value', $call->returns());
    }

    public function testParamsCanBePassedInAsScalar()
    {
        $call = new Sham_Call('call name', 'param 1');
        $this->assertEquals(array('param 1'), $call->params);
    }

    public function testShouldThrowIfSet()
    {
        $this->setExpectedException('Sham_Exception');
        $call = new Sham_Call('call name', array(), 'return value');
        $call->throws('Sham_Exception');
        $call();
    }

    public function testShouldThrowExceptionWhenCalledWithNoParam()
    {
        $this->setExpectedException('Sham_Exception');
        $call = new Sham_Call('call name', array(), 'return value');
        $call->throws();
        $call();
    }

    public function testShouldThrowExceptionWhenCalledWithObject()
    {
        $this->setExpectedException('TestException');

        $call = new Sham_Call('call name', array(), 'return value');
        $exception = new TestException();
        $call->throws($exception);

        try {
            $call();
        } catch (Exception $e) {
            $this->assertTrue($exception === $e);
            throw $e;
        }
    }
}

class TestException extends Exception {}
