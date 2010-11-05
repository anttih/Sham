<?php
require_once 'sham/CallList.php';

use sham\CallList;

class Sham_CallListWithMultipleCallsTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $list = new CallList();
        $list->add('call name', array('param1'), 'return value');
        $list->add('call name', array('param1', 'param2'), 'return value');
        $this->list = $list;
    }

    public function testShouldReturnCallsWithSameName()
    {
        $this->assertEquals(2, count($this->list->calls('call name')));
    }

    public function testShouldReturnCallWithOneParam()
    {
        $this->assertEquals(1, count($this->list->calls('call name', 'param1')));
    }

    public function testShouldReturnCallWhenAllMatch()
    {
        $calls = $this->list->calls('call name', 'param1', 'param2');
        $this->assertEquals(1, count($calls));
    }

    public function testShouldIgnoreFirstArg()
    {
        $calls = $this->list->calls('call name', Sham::any());
        $this->assertEquals(1, count($calls));
    }

    public function testShouldIgnoreSecondArg()
    {
        $calls = $this->list->calls('call name', 'param1', Sham::any());
        $this->assertEquals(1, count($calls));
    }

    public function testOnceShouldReturnFalse()
    {
        $this->assertFalse($this->list->once());
    }

    public function testShouldReturnTrueForTimesTwo()
    {
        $this->assertTrue($this->list->times(2));
    }
}
