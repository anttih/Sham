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

    public function testOffsetSetShouldSetValue()
    {
        $mock = new Mocker_Mock();
        $mock[0] = 1;
        $data = $mock->mockerGetData();
        $this->assertEquals(1, $data[0]);
    }

    public function testShouldRecordOffsetSet()
    {
        $mock = new Mocker_Mock();
        $mock[0] = 1;
        $this->assertTrue($mock->calls('offsetSet', 0, 1)->once());
    }

    public function testOffsetGetShouldGetValue()
    {
        $mock = new Mocker_Mock();
        $mock->mockerSetData(array(1));
        $this->assertEquals(1, $mock[0]);
    }

    public function testShouldRecordOffsetGet()
    {
        $mock = new Mocker_Mock();
        $mock->mockerSetData(array(1));

        // action
        $mock[0];

        $list = $mock->calls('offsetGet', 0);
        $this->assertTrue($list->once());

        // records return value
        $this->assertEquals(1, $list->calls[0]->return_value);
    }

    public function testShouldRecordIsset()
    {
        $mock = new Mocker_Mock();
        $mock->mockerSetData(array(1, 2));
        isset($mock[0]);
        $this->assertTrue($mock->calls('offsetExists', 0)->once());
    }

    public function testShouldIssetReturnValueAsCallReturnValue()
    {
        $mock = new Mocker_Mock();
        $mock->mockerSetData(array(1, 2));
        $isset = isset($mock[0]);
        $this->assertTrue($isset);
        $this->assertTrue($mock->calls('offsetExists', 0)->calls[0]->return_value);
    }

    public function testShouldRecordUnset()
    {
        $mock = new Mocker_Mock();
        $mock->mockerSetData(array(1, 2));
        unset($mock[0]);
        $this->assertTrue($mock->calls('offsetUnset', 0)->once());
    }
}

