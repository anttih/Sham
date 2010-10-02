<?php
require_once 'Sham/CallList.php';

use Sham\CallList,
    Sham\Call;

class Sham_CallListTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->list = new CallList();
    }

    public function testCanAddCallObjects()
    {
        $this->list->add(new Call('method', array(), ''));
        $this->assertEquals(1, count($this->list->calls));
    }

    public function testShouldAddCallWithNameParamsAndReturnValue()
    {
        $this->list->add('call name', array('value'), 'return value');
        $call = $this->list->calls[0];
        $this->assertEquals('call name', $call->name);
        $this->assertEquals(array('value'), $call->params);
        $this->assertEquals('return value', $call->return_value);
    }

    public function testEmptyCallListHasZeroCalls()
    {
        $this->assertEquals(0, count(new CallList()));
    }

    public function testShouldReturnCallListFromCalls()
    {
        $this->assertTrue($this->list->calls() instanceof CallList);
    }

    public function testShouldMatchOneCallByCallName()
    {
        $this->list->add('call name', array(), 'return value');
        $this->assertTrue($this->list->calls('call name')->once());
    }

    public function testEmptyCallNameMatchesAll()
    {
        $this->list->add('method 1', array(), 'return value');
        $this->list->add('method 2', array('param 1'), 'return value');
        $this->assertEquals(2, count($this->list->calls()));
    }

    public function testShouldMatchByCallName()
    {
        $this->list->add('call name', array(), 'return value');
        $this->list->add('call name 2', array(), 'return value');

        $this->assertEquals(1, count($this->list->calls('call name')));
    }

    public function testShouldMatchByCallNameWhenOneParam()
    {
        $this->list->add('call name', array('param1'), 'return value');
        $this->assertEquals(1, count($this->list->calls('call name')));
    }

    public function testShouldMatchFirstParamAsAny()
    {
        $this->list->add('call name', array('param 1'), 'return value');
        $this->assertTrue($this->list->calls('call name', Sham::any())->once());
    }

    public function testShouldMatchFirstParamAsAnyWhenTwoParams()
    {
        $this->list->add('call name', array('param 1', 'param 2'), 'return value');
        $this->assertTrue($this->list->calls('call name', Sham::any(), 'param 2')->once());
    }

    public function testShouldMatchLastParamAsAny()
    {
        $this->list->add('call name', array('param 1', 'param 2', 'param 3'), 'return value');
        $this->assertTrue($this->list->calls('call name', 'param 1', 'param 2', Sham::any())->once());
    }

    public function testFirstShouldReturnFalseIfNoCalls()
    {
        $this->assertFalse($this->list->calls()->first());
    }

    public function testFirstShouldReturnFirstCall()
    {
        $this->list->add('first call');
        $this->list->add('second call');
        $this->assertEquals('first call', $this->list->calls()->first()->name);
    }
    
    public function testHasShortcutForNeverCalled()
    {
        $this->assertTrue($this->list->never());
        $this->list->add('call');
        $this->assertFalse($this->list->never());
    }
}

