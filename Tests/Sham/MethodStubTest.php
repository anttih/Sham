<?php
require_once 'Sham/MethodStub.php';
class Sham_MethodStubText extends PHPUnit_Framework_TestCase
{
    
    public function testShouldReturnReturnValue()
    {
        $stub = new Sham_MethodStub('method name', 'return value');
        $this->assertEquals('return value', $stub());
    }

    public function testShouldBeAbleToSetReturnValue()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->returns('return value');
        $this->assertEquals('return value', $stub());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $stub = new Sham_MethodStub('method name', false);
        $this->assertEquals(false, $stub());
    }

    public function testShouldThrowIfSet()
    {
        $this->setExpectedException('Sham_Exception');
        $stub = new Sham_MethodStub('method name', 'return value');
        $stub->throws('Sham_Exception');
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithNoParam()
    {
        $this->setExpectedException('Sham_Exception');
        $stub = new Sham_MethodStub('method name', 'return value');
        $stub->throws();
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithObject()
    {
        $this->setExpectedException('TestException');

        $stub = new Sham_MethodStub('method name', 'return value');
        $exception = new TestException();
        $stub->throws($exception);

        try {
            $stub();
        } catch (Exception $e) {
            $this->assertTrue($exception === $e);
            throw $e;
        }
    }
}

class TestException extends Exception {}
