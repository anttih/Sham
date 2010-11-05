<?php
require_once 'sham/Stub.php';

use sham\CallList,
    sham\Stub,
    sham\Method;

class Sham_StubTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->mock = new Stub();
    }

    public function testCallingMethodsShouldReturnNewStubByDefault()
    {
        $one = $this->mock->method();
        $this->assertTrue($one instanceof Stub);
    }
    
    public function testCallingMethodsAfterGetShouldReturnNewStubByDefault()
    {
        $this->mock->method;
        $one = $this->mock->method();
        $this->assertTrue($one instanceof Stub);
    }

    public function testCallsShouldReturnSameValue()
    {
        $this->assertSame(
            $this->mock->method(),
            $this->mock->method()
        );
    }

    public function testShouldKeepReturningSameValueWhenReturnValueSet()
    {
        $this->mock->method->returns('return value');
        $this->assertSame(
            $this->mock->method(),
            $this->mock->method()
        );
    }

    public function testShouldReturnDefaultValueWhenSetSpecificReturnValueForSomeParams()
    {
        $this->mock->method->given('foo')->returns('bar');
        $this->assertTrue($this->mock->method() instanceof Stub);
    }
    
    public function testCallingMethodsShouldSendParameters()
    {
        $this->mock->method->given(1)->returns(2);
        $this->assertEquals(2, $this->mock->method(1));
    }
    
    public function testGettingAPropertyWithoutSettingShouldReturnMethodObject()
    {
        $this->assertTrue($this->mock->property instanceof Method);
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

    public function testShouldGetValueSetWithArrayAccess()
    {
        $this->mock['key'] = 'value';
        $this->assertEquals('value', $this->mock->key);
    }

    public function testShouldGetValueSetWithOverloading()
    {
        $this->mock->key = 'value';
        $this->assertEquals('value', $this->mock['key']);
    }

    public function testShouldRecordPropertySet()
    {
        $this->mock->key = 'value';
        $this->assertTrue($this->mock->calls('__set', 'value')->once());
    }

    public function testShouldReturnFromChildStub()
    {
        $this->mock->method->returns("return value");
        $this->assertEquals("return value", $this->mock->method());
    }

    public function testShouldRecordInvoke()
    {
        $mock = new Stub();
        $mock();
        $this->assertTrue($mock->calls('__invoke')->once());
    }

    public function testShouldRecordInvokeParams()
    {
        $mock = new Stub();
        $mock('param 1');
        $this->assertTrue($mock->calls('__invoke', 'param 1')->once());
    }

    public function testInvokeShouldReturnNewStubWhenNoReturnValue()
    {
        $mock = new Stub();
        $this->assertTrue($mock() instanceof Stub);
        $this->assertTrue($mock() !== $mock);
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $mock = new Stub();
        $mock->returns('return value');
        $this->assertSame('return value', $mock());
    }
    
    public function testShouldReturnSameMethodOnEachRequest()
    {
        $mock = new Stub();
        $one = $mock->foo;
        $two = $mock->foo;
        $this->assertSame($one, $two);
    }

    public function testShouldRecordCalls()
    {
        $this->mock->method0();
        $this->mock->method1();
        $this->assertEquals(2, count($this->mock->calls()));
    }

    public function testShouldProxyCallsToCallList()
    {
        $this->assertTrue($this->mock->calls() instanceof CallList);
    }

    public function testMethodCallWithReturnValueShouldNotChangeParamsAfterSecondCall()
    {
        $this->mock->method->returns('return value');
        $this->mock->method('first call');
        $this->mock->method('second call');
        $this->assertTrue($this->mock->calls('method', 'first call')->once());
    }

    public function testOffsetSetShouldSetValue()
    {
        $this->mock[0] = 1;
        $data = $this->mock->shamGetData();
        $this->assertEquals(1, $data[0]);
    }

    public function testShouldRecordOffsetSet()
    {
        $this->mock[0] = 1;
        $this->assertTrue($this->mock->calls('offsetSet', 0, 1)->once());
    }

    public function testOffsetGetShouldGetValue()
    {
        $this->mock->shamSetData(array(1));
        $this->assertEquals(1, $this->mock[0]);
    }

    public function testShouldRecordOffsetGet()
    {
        $this->mock->shamSetData(array(1));

        // action
        $this->mock[0];

        $list = $this->mock->calls('offsetGet', 0);
        $this->assertTrue($list->once());

        // records return value
        $this->assertEquals(1, $list->calls[0]->return_value);
    }

    public function testShouldRecordOffsetExists()
    {
        $this->mock->shamSetData(array(1, 2));
        isset($this->mock[0]);
        $this->assertTrue($this->mock->calls('offsetExists', 0)->once());
    }

    public function testIssetShouldReturnValueAsCallReturnValue()
    {
        $this->mock->shamSetData(array(1, 2));
        $isset = isset($this->mock[0]);
        $this->assertTrue($isset);
        $this->assertTrue($this->mock->calls('offsetExists', 0)->calls[0]->return_value);
    }

    public function testShouldRecordUnset()
    {
        $this->mock->shamSetData(array(1, 2));
        unset($this->mock[0]);
        $this->assertTrue($this->mock->calls('offsetUnset', 0)->once());
    }

    public function testToStringShouldReturnClassNameByDefault()
    {
        $mock = new Stub();
        $this->assertEquals(get_class($mock), (string) $mock);
    }

    public function testCanSetToStringReturnValue()
    {
        $mock = new Stub();
        $mock->__toString->returns('to string');
        $this->assertEquals('to string', (string) $mock);
    }

    public function test__issetShouldReturnTrueForSetProperty()
    {
        $this->mock->prop = false;
        $this->assertTrue(isset($this->mock->prop));
    }

    public function test__issetShouldRecordCallWhenSetProperty()
    {
        $this->mock->prop = false;
        isset($this->mock->prop);
        $this->assertTrue($this->mock->calls('__isset', 'prop')->once());
    }

    public function test__issetShouldRecordReturnValue()
    {
        $this->mock->prop = false;
        isset($this->mock->prop);
        $ret = $this->mock->calls('__isset', 'prop')->calls[0]->return_value;
        $this->assertTrue($ret);
    }

    public function test__issetShouldRecordWhenPropertyNotSet()
    {
        isset($this->mock->prop);
        $this->assertTrue($this->mock->calls('__isset', 'prop')->once());
    }
    
    public function test__unsetShouldRecord()
    {
        unset($this->mock->prop);
        $this->assertTrue($this->mock->calls('__unset', 'prop')->once());
    }

    public function test__unsetShouldUnsetProp()
    {
        $this->mock->prop = 'value';
        unset($this->mock->prop);
        $this->assertFalse(isset($this->mock->prop));
    }
}

