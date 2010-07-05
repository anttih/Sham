<?php
require_once 'Mocker/CallList.php';
class Mocker_CallListTest extends PHPUnit_Framework_TestCase
{
    public function testShouldAddCallWithNameParamsAndReturnValue()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array('value'), 'return value');
        $this->assertEquals('call name', $list->calls[0][0]);
        $this->assertEquals(array('value'), $list->calls[0][1]);
        $this->assertEquals('return value', $list->calls[0][2]);
    }

    public function testEmptyCallListHasZeroCalls()
    {
        $this->assertEquals(0, count(new Mocker_CallList()));
    }

    public function testShouldReturnCallListFromCalls()
    {
        $list = new Mocker_CallList();
        $this->assertTrue($list->calls() instanceof Mocker_CallList);
    }

    public function testShouldFilterOnName()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array(), 'return value');
        $list->add('call name 2', array(), 'return value');

        $this->assertEquals(1, count($list->calls('call name')));
    }

    public function testGivenAListWithOneCallWithArg_ItShouldReturnItWhithNoArgs()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array('param1'), 'return value');
        $this->assertEquals(1, count($list->calls('call name')));
    }

    public function testWhenOnlyOneCall_OnceShouldReturnTrue()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array(), 'return value');
        $this->assertTrue($list->calls('call name')->once());
    }
}
