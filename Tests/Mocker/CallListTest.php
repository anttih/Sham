<?php
require_once 'Mocker/CallList.php';
class Mocker_CallListTest extends PHPUnit_Framework_TestCase
{
    public function testCanAddCallObjects()
    {
        $list = new Mocker_CallList();
        $list->add(new Mocker_Call('method'));
        $this->assertEquals(1, count($list->calls));
    }

    public function testShouldAddCallWithNameParamsAndReturnValue()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array('value'), 'return value');
        $call = $list->calls[0];
        $this->assertEquals('call name', $call->name);
        $this->assertEquals(array('value'), $call->params);
        $this->assertEquals('return value', $call->return_value);
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

    public function testShouldReturnAllCallsWhenNoMethodGiven()
    {
        $list = new Mocker_CallList();
        $list->add('method 1', array(), 'return value');
        $list->add('method 2', array(), 'return value');
        $this->assertEquals(2, count($list->calls()));
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

    public function testIgnoreOptionalParamsThatWereNotPassedInWhenAdding()
    {
        $list = new Mocker_CallList();
        $list->add('call name', array('param', Mocker::NO_VALUE_PASSED), 'return value');
        $this->assertTrue(count($list->calls[0]->params) === 1);
    }
}
