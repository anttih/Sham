<?php
require_once 'sham/Sham.php';
require_once 'sham/Method.php';

use sham\Sham,
    sham\Method;

class Sham_MethodTest extends PHPUnit_Framework_TestCase
{

    public function testShouldThrowExceptionIfCalledWithoutStubbing()
    {
        $stub = new Method('method name');
        $this->setExpectedException('sham\Exception', 'Nothing stubbed');
        $stub();
    }

    public function testShouldBeAbleToSetReturnValue()
    {
        $stub = new Method('method name');
        $stub->returns('return value');
        $this->assertEquals('return value', $stub());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $stub = new Method('method name');
        $stub->returns(false);
        $this->assertEquals(false, $stub());
    }

    public function testShouldThrowIfSet()
    {
        $this->setExpectedException('sham\Exception');
        $stub = new Method('method name');
        $stub->throws('sham\Exception');
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithNoParam()
    {
        $this->setExpectedException('sham\Exception');
        $stub = new Method('method name');
        $stub->throws();
        $stub();
    }

    public function testShouldThrowExceptionWhenCalledWithObject()
    {
        $this->setExpectedException('GoodException');

        $stub = new Method('method name');
        $exception = new GoodException();
        $stub->throws($exception);

        try {
            $stub();
        } catch (Exception $e) {
            $this->assertTrue($exception === $e);
            throw $e;
        }
    }
    
    public function testShouldAllowClosureAsReturnValue()
    {
        $stub = new Method('method name');
        $stub->returns(function () {});
        $this->assertTrue($stub() instanceof Closure);
    }

    public function testShouldAllowArbitraryActions()
    {
        $func = function($a, $b) use (&$args) {
            return $a + $b;
        };
        
        $stub = new Method('method name');
        $stub->does($func);
        
        $this->assertEquals(4, $stub(2, 2));
    }
    
    public function testShouldAllowDifferentReturnValuesForSpecificParameters()
    {
        $stub = new Method('method name');
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
        $stub = new Method('method name');
        $stub->throws('BadException');
        $stub->given(Sham::any(), 'good')->throws('GoodException');
        $stub->given(Sham::any(), 'bad')->throws('BadException');
        
        $this->setExpectedException('GoodException');
        $stub(3, 'good');
    }
    
    public function testShouldAllowArbitraryActionsForSpecificParameters()
    {
        $stub = new Method('method name');
        $stub->returns('default return');
        $stub->given(Sham::any(), 10)->does(function() { return func_get_args(); });
        
        $this->assertEquals('default return', $stub(5));
        $this->assertEquals(array(5, 10), $stub(5, 10));
    }
    
    
    public function testLaterStubbingsShouldOverrideEarlierOverlappingOnes()
    {
        $stub = new Method('method name');
        
        $stub->given(Sham::any(), 1)->returns(2);
        $stub->given(5, 1)->returns(3);
        
        $this->assertEquals(3, $stub(5, 1));
    }
}

class GoodException extends Exception {}
class BadException extends Exception {}
