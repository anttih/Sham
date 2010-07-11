<?php
require_once 'Sham/CallList.php';
class Sham_CallListTest extends PHPUnit_Framework_TestCase
{
    public function testCanAddCallObjects()
    {
        $list = new Sham_CallList();
        $list->add(new Sham_Call('method'));
        $this->assertEquals(1, count($list->calls));
    }

    public function testShouldAddCallWithNameParamsAndReturnValue()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('value'), 'return value');
        $call = $list->calls[0];
        $this->assertEquals('call name', $call->name);
        $this->assertEquals(array('value'), $call->params);
        $this->assertEquals('return value', $call->return_value);
    }

    public function testEmptyCallListHasZeroCalls()
    {
        $this->assertEquals(0, count(new Sham_CallList()));
    }

    public function testShouldReturnCallListFromCalls()
    {
        $list = new Sham_CallList();
        $this->assertTrue($list->calls() instanceof Sham_CallList);
    }

    public function testShouldMatchOneCallByCallName()
    {
        $list = new Sham_CallList();
        $list->add('call name', array(), 'return value');
        $this->assertTrue($list->calls('call name')->once());
    }

    public function testEmptyCallNameMatchesAll()
    {
        $list = new Sham_CallList();
        $list->add('method 1', array(), 'return value');
        $list->add('method 2', array('param 1'), 'return value');
        $this->assertEquals(2, count($list->calls()));
    }

    public function testShouldMatchByCallName()
    {
        $list = new Sham_CallList();
        $list->add('call name', array(), 'return value');
        $list->add('call name 2', array(), 'return value');

        $this->assertEquals(1, count($list->calls('call name')));
    }

    public function testShouldMatchByCallNameWhenOneParam()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('param1'), 'return value');
        $this->assertEquals(1, count($list->calls('call name')));
    }

    public function testIgnoreOptionalParamsThatWereNotPassedInWhenAdding()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('param', Sham::NO_VALUE_PASSED), 'return value');
        $this->assertTrue(count($list->calls[0]->params) === 1);
    }

    public function testShouldMatchFirstParamAsAny()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('param 1'), 'return value');
        $this->assertTrue($list->calls('call name', Sham::ANY)->once());
    }

    public function testShouldMatchFirstParamAsAnyWhenTwoParams()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('param 1', 'param 2'), 'return value');
        $this->assertTrue($list->calls('call name', Sham::ANY, 'param 2')->once());
    }

    public function testShouldMatchLastParamAsAny()
    {
        $list = new Sham_CallList();
        $list->add('call name', array('param 1', 'param 2', 'param 3'), 'return value');
        $this->assertTrue($list->calls('call name', 'param 1', 'param 2', Sham::ANY)->once());
    }

    public function testFirstShouldReturnFalseIfNoCalls()
    {
        $list = new Sham_CallList();
        $this->assertFalse($list->calls()->first());
    }

    public function testFirstShouldReturnFirstCall()
    {
        $list = new Sham_CallList();
        $list->add('first call');
        $list->add('second call');
        $this->assertEquals('first call', $list->calls()->first()->name);
    }
}
