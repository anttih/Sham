<?php
require_once 'Sham/Mock.php';

class Sham_MockTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->mock = new Sham_Mock();
    }

    public function testCallingMethodsShouldReturnSham_Mock()
    {
        $ret = $this->mock->method();
        $this->assertTrue($ret instanceof Sham_Mock);
    }
    
    public function testGettingAPropertyWithoutSettingShouldReturnCallObject()
    {
        $this->assertTrue($this->mock->property instanceof Sham_Call);
    }

    public function testGettingAPropertyWithoutSettingShouldNotRecord()
    {
        $value = $this->mock->key;
        $this->assertFalse($this->mock->calls('__get', 'key')->once());
    }

    public function testShouldRecordPropertyGetWhenSet()
    {
        $this->mock->key = 'value';
        $value = $this->mock->key;

        $this->assertTrue($this->mock->calls('__get', 'key')->once());

        $call = $this->mock->calls('__get', 'key')->calls[0];
        $this->assertEquals('value', $call->return_value);
    }

    public function testShouldReturnSetPropertyValue()
    {
        $this->mock->key = "key value";
        $this->assertEquals("key value", $this->mock->key);
    }

    public function testShouldRecordPropertySet()
    {
        $this->mock->key = 'value';
        $this->assertTrue($this->mock->calls('__set', 'value')->once());
    }

    public function testShouldReturnFromChildSham_Mock()
    {
        $this->mock->method->returns("return value");
        $this->assertEquals("return value", $this->mock->method());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $this->mock->someMethod->returns(false);
        $this->assertEquals(false, $this->mock->someMethod());
    }

    public function testShouldRecordInvoke()
    {
        $mock = new Sham_Mock();
        $mock();
        $this->assertTrue($mock->calls('__invoke')->once());
    }

    public function testInvokeShouldReturnThisMockIfNoReturnValueSet()
    {
        $mock = new Sham_Mock();
        $this->assertSame($mock, $mock());
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $mock = new Sham_Mock();
        $mock->returns('return value');
        $this->assertSame('return value', $mock());
    }

    public function testShouldRecordCalls()
    {
        $this->mock->method0();
        $this->mock->method1();
        $this->assertEquals(2, count($this->mock->calls()));
    }

    public function testShouldRecordReturnValue()
    {
        $this->mock->method();
        $this->assertTrue($this->mock->calls()->calls[0]->return_value instanceof Sham_Mock);
    }

    public function testShouldProxyCallsToCallList()
    {
        $this->assertTrue($this->mock->calls() instanceof Sham_CallList);
    }

    public function testOffsetSetShouldSetValue()
    {
        $this->mock[0] = 1;
        $data = $this->mock->mockerGetData();
        $this->assertEquals(1, $data[0]);
    }

    public function testShouldRecordOffsetSet()
    {
        $this->mock[0] = 1;
        $this->assertTrue($this->mock->calls('offsetSet', 0, 1)->once());
    }

    public function testOffsetGetShouldGetValue()
    {
        $this->mock->mockerSetData(array(1));
        $this->assertEquals(1, $this->mock[0]);
    }

    public function testShouldRecordOffsetGet()
    {
        $this->mock->mockerSetData(array(1));

        // action
        $this->mock[0];

        $list = $this->mock->calls('offsetGet', 0);
        $this->assertTrue($list->once());

        // records return value
        $this->assertEquals(1, $list->calls[0]->return_value);
    }

    public function testShouldRecordIsset()
    {
        $this->mock->mockerSetData(array(1, 2));
        isset($this->mock[0]);
        $this->assertTrue($this->mock->calls('offsetExists', 0)->once());
    }

    public function testShouldIssetReturnValueAsCallReturnValue()
    {
        $this->mock->mockerSetData(array(1, 2));
        $isset = isset($this->mock[0]);
        $this->assertTrue($isset);
        $this->assertTrue($this->mock->calls('offsetExists', 0)->calls[0]->return_value);
    }

    public function testShouldRecordUnset()
    {
        $this->mock->mockerSetData(array(1, 2));
        unset($this->mock[0]);
        $this->assertTrue($this->mock->calls('offsetUnset', 0)->once());
    }
}

