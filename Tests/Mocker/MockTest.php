<?php
require_once 'Mocker/Mock.php';

class Mocker_MockTest extends PHPUnit_Framework_TestCase
{
    public function testCallingMethodsShouldReturnMocker_Mock()
    {
        $mocker = new Mocker_Mock();
        $ret = $mocker->method();
        $this->assertTrue($ret instanceof Mocker_Mock);
    }
    
    public function testGettingAPropertyWithoutSettingShouldReturnCallObject()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue($mocker->property instanceof Mocker_Call);
    }

    public function testGettingAPropertyWithoutSettingShouldNotRecord()
    {
        $mocker = new Mocker_Mock();
        $value = $mocker->key;
        $this->assertFalse($mocker->calls('__get', 'key')->once());
    }

    public function testShouldRecordPropertyGetWhenSet()
    {
        $mocker = new Mocker_Mock();
        $mocker->key = 'value';
        $value = $mocker->key;

        $this->assertTrue($mocker->calls('__get', 'key')->once());

        $call = $mocker->calls('__get', 'key')->calls[0];
        $this->assertEquals('value', $call->return_value);
    }

    public function testShouldReturnSetPropertyValue()
    {
        $mocker = new Mocker_Mock();
        $mocker->key = "key value";
        $this->assertEquals("key value", $mocker->key);
    }

    public function testShouldRecordPropertySet()
    {
        $mocker = new Mocker_Mock();
        $mocker->key = 'value';
        $this->assertTrue($mocker->calls('__set', 'value')->once());
    }

    public function testShouldReturnFromChildMocker_Mock()
    {
        $mocker = new Mocker_Mock();
        $mocker->method->setReturn("return value");
        $this->assertEquals("return value", $mocker->method());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $mocker = new Mocker_Mock();
        $mocker->someMethod->setReturn(false);
        $this->assertEquals(false, $mocker->someMethod());
    }

    public function testShouldBeAbleToGetArrayElements()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue($mocker['irrelevant'] instanceof Mocker_Mock);
    }

    public function testShouldReturnTrueForIsset()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue(isset($mocker['irrelevant']));
    }

    public function testShouldRecordInvoke()
    {
        $mocker = new Mocker_Mock();
        $mocker();
        $this->assertTrue($mocker->calls('__invoke')->once());
    }

    public function testShouldReturnThisMockIfNoReturnValueSet()
    {
        $mocker = new Mocker_Mock();
        $this->assertSame($mocker, $mocker());
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $mocker = new Mocker_Mock();
        $mocker->setReturn('return value');
        $this->assertSame('return value', $mocker());
    }

    public function testShouldRecordCalls()
    {
        $mocker = new Mocker_Mock();
        $mocker->method0();
        $mocker->method1();
        $this->assertEquals(2, count($mocker->calls()));
    }

    public function testShouldRecordReturnValue()
    {
        $mocker = new Mocker_Mock();
        $mocker->method();
        $this->assertTrue($mocker->calls()->calls[0]->return_value instanceof Mocker_Mock);
    }

    public function testShouldProxyCallsToCallList()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue($mocker->calls() instanceof Mocker_CallList);
    }
}

