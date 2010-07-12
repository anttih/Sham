<?php
require_once 'Sham.php';
require_once 'Sham/MethodStub.php';
class Sham_MethodStubTest extends PHPUnit_Framework_TestCase
{

    public function testShouldThrowExceptionIfCalledWithoutStubbing()
    {
        $stub = new Sham_MethodStub('method name');
        $this->setExpectedException('Sham_Exception', 'Nothing stubbed');
        $stub();
    }

    public function testShouldBeAbleToSetReturnValue()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->returns('return value');
        $this->assertEquals('return value', $stub());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->returns(false);
        $this->assertEquals(false, $stub());
    }

    public function testShouldThrowIfSet()
    {
        $this->setExpectedException('Sham_Exception');
        $stub = new Sham_MethodStub('method name');
        $stub->throws('Sham_Exception');
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithNoParam()
    {
        $this->setExpectedException('Sham_Exception');
        $stub = new Sham_MethodStub('method name');
        $stub->throws();
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithObject()
    {
        $this->setExpectedException('GoodException');

        $stub = new Sham_MethodStub('method name');
        $exception = new GoodException();
        $stub->throws($exception);

        try {
            $stub();
        } catch (Exception $e) {
            $this->assertTrue($exception === $e);
            throw $e;
        }
    }
    
    public function testShouldAllowArbitraryActions()
    {
        $func = function($a, $b) use (&$args) {
            return $a + $b;
        };
        
        $stub = new Sham_MethodStub('method name');
        $stub->does($func);
        
        $this->assertEquals(4, $stub(2, 2));
    }
    
    public function testShouldAllowDifferentReturnValuesForSpecificParameters()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->returns('default return');
        $stub->given(1, 2, 3)->returns('one-two-three');
        $stub->given(3, Sham::any())->returns('three-something');
        
        $this->assertEquals('one-two-three', $stub(1, 2, 3));
        $this->assertEquals('three-something', $stub(3, 1));
        $this->assertEquals('three-something', $stub(3, null));
        $this->assertEquals('default return', $stub(3, 4, 5));
    }
    
    public function testShouldAllowThrowingForSpecificParameters()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->throws('BadException');
        $stub->given(Sham::any(), 'good')->throws('GoodException');
        $stub->given(Sham::any(), 'bad')->throws('BadException');
        
        $this->setExpectedException('GoodException');
        $stub(3, 'good');
    }
    
    public function testShouldAllowArbitraryActionsForSpecificParameters()
    {
        $stub = new Sham_MethodStub('method name');
        $stub->returns('default return');
        $stub->given(Sham::any(), 10)->does(function() { return func_get_args(); });
        
        $this->assertEquals('default return', $stub(5));
        $this->assertEquals(array(5, 10), $stub(5, 10));
    }
    
    
    public function testLaterStubbingsShouldOverrideEarlierOverlappingOnes()
    {
        $stub = new Sham_MethodStub('method name');
        
        $stub->given(Sham::any(), 1)->returns(2);
        $stub->given(5, 1)->returns(3);
        
        $this->assertEquals(3, $stub(5, 1));
    }
}

class GoodException extends Exception {}
class BadException extends Exception {}
