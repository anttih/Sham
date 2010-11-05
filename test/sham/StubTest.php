<?php
require_once 'sham/Stub.php';

use sham\CallList,
    sham\Stub,
    sham\Method;

class Sham_StubTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->stub = new Stub();
    }

    public function testCallingMethodsShouldReturnNewStubByDefault()
    {
        $one = $this->stub->method();
        $this->assertTrue($one instanceof Stub);
    }
    
    public function testCallingMethodsAfterGetShouldReturnNewStubByDefault()
    {
        $this->stub->method;
        $one = $this->stub->method();
        $this->assertTrue($one instanceof Stub);
    }

    public function testCallsShouldReturnSameValue()
    {
        $this->assertSame(
            $this->stub->method(),
            $this->stub->method()
        );
    }

    public function testShouldKeepReturningSameValueWhenReturnValueSet()
    {
        $this->stub->method->returns('return value');
        $this->assertSame(
            $this->stub->method(),
            $this->stub->method()
        );
    }

    public function testShouldReturnDefaultValueWhenSetSpecificReturnValueForSomeParams()
    {
        $this->stub->method->given('foo')->returns('bar');
        $this->assertTrue($this->stub->method() instanceof Stub);
    }
    
    public function testCallingMethodsShouldSendParameters()
    {
        $this->stub->method->given(1)->returns(2);
        $this->assertEquals(2, $this->stub->method(1));
    }
    
    public function testGettingAPropertyWithoutSettingShouldReturnMethodObject()
    {
        $this->assertTrue($this->stub->property instanceof Method);
    }

    public function testGettingAPropertyWithoutSettingShouldNotRecord()
    {
        $value = $this->stub->key;
        $this->assertFalse($this->stub->got('__get', 'key')->once());
    }

    public function testShouldRecordPropertyGetWhenSet()
    {
        $this->stub->key = 'value';
        $value = $this->stub->key;

        $this->assertTrue($this->stub->got('__get', 'key')->once());

        $call = $this->stub->got('__get', 'key')->calls[0];
        $this->assertEquals('value', $call->return_value);
    }

    public function testShouldReturnSetPropertyValue()
    {
        $this->stub->key = "key value";
        $this->assertEquals("key value", $this->stub->key);
    }

    public function testShouldGetValueSetWithArrayAccess()
    {
        $this->stub['key'] = 'value';
        $this->assertEquals('value', $this->stub->key);
    }

    public function testShouldGetValueSetWithOverloading()
    {
        $this->stub->key = 'value';
        $this->assertEquals('value', $this->stub['key']);
    }

    public function testShouldRecordPropertySet()
    {
        $this->stub->key = 'value';
        $this->assertTrue($this->stub->got('__set', 'value')->once());
    }

    public function testShouldReturnFromChildStub()
    {
        $this->stub->method->returns("return value");
        $this->assertEquals("return value", $this->stub->method());
    }

    public function testShouldRecordInvoke()
    {
        $stub = new Stub();
        $stub();
        $this->assertTrue($stub->got('__invoke')->once());
    }

    public function testShouldRecordInvokeParams()
    {
        $stub = new Stub();
        $stub('param 1');
        $this->assertTrue($stub->got('__invoke', 'param 1')->once());
    }

    public function testInvokeShouldReturnNewStubWhenNoReturnValue()
    {
        $stub = new Stub();
        $this->assertTrue($stub() instanceof Stub);
        $this->assertTrue($stub() !== $stub);
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $stub = new Stub();
        $stub->returns('return value');
        $this->assertSame('return value', $stub());
    }
    
    public function testShouldReturnSameMethodOnEachRequest()
    {
        $stub = new Stub();
        $one = $stub->foo;
        $two = $stub->foo;
        $this->assertSame($one, $two);
    }

    public function testShouldRecordCalls()
    {
        $this->stub->method0();
        $this->stub->method1();
        $this->assertEquals(2, count($this->stub->got()));
    }

    public function testShouldProxyCallsToCallList()
    {
        $this->assertTrue($this->stub->got() instanceof CallList);
    }

    public function testMethodCallWithReturnValueShouldNotChangeParamsAfterSecondCall()
    {
        $this->stub->method->returns('return value');
        $this->stub->method('first call');
        $this->stub->method('second call');
        $this->assertTrue($this->stub->got('method', 'first call')->once());
    }

    public function testOffsetSetShouldSetValue()
    {
        $this->stub[0] = 1;
        $data = $this->stub->shamGetData();
        $this->assertEquals(1, $data[0]);
    }

    public function testShouldRecordOffsetSet()
    {
        $this->stub[0] = 1;
        $this->assertTrue($this->stub->got('offsetSet', 0, 1)->once());
    }

    public function testOffsetGetShouldGetValue()
    {
        $this->stub->shamSetData(array(1));
        $this->assertEquals(1, $this->stub[0]);
    }

    public function testShouldRecordOffsetGet()
    {
        $this->stub->shamSetData(array(1));

        // action
        $this->stub[0];

        $list = $this->stub->got('offsetGet', 0);
        $this->assertTrue($list->once());

        // records return value
        $this->assertEquals(1, $list->calls[0]->return_value);
    }

    public function testShouldRecordOffsetExists()
    {
        $this->stub->shamSetData(array(1, 2));
        isset($this->stub[0]);
        $this->assertTrue($this->stub->got('offsetExists', 0)->once());
    }

    public function testIssetShouldReturnValueAsCallReturnValue()
    {
        $this->stub->shamSetData(array(1, 2));
        $isset = isset($this->stub[0]);
        $this->assertTrue($isset);
        $this->assertTrue($this->stub->got('offsetExists', 0)->calls[0]->return_value);
    }

    public function testShouldRecordUnset()
    {
        $this->stub->shamSetData(array(1, 2));
        unset($this->stub[0]);
        $this->assertTrue($this->stub->got('offsetUnset', 0)->once());
    }

    public function testToStringShouldReturnClassNameByDefault()
    {
        $stub = new Stub();
        $this->assertEquals(get_class($stub), (string) $stub);
    }

    public function testCanSetToStringReturnValue()
    {
        $stub = new Stub();
        $stub->__toString->returns('to string');
        $this->assertEquals('to string', (string) $stub);
    }

    public function test__issetShouldReturnTrueForSetProperty()
    {
        $this->stub->prop = false;
        $this->assertTrue(isset($this->stub->prop));
    }

    public function test__issetShouldRecordCallWhenSetProperty()
    {
        $this->stub->prop = false;
        isset($this->stub->prop);
        $this->assertTrue($this->stub->got('__isset', 'prop')->once());
    }

    public function test__issetShouldRecordReturnValue()
    {
        $this->stub->prop = false;
        isset($this->stub->prop);
        $ret = $this->stub->got('__isset', 'prop')->calls[0]->return_value;
        $this->assertTrue($ret);
    }

    public function test__issetShouldRecordWhenPropertyNotSet()
    {
        isset($this->stub->prop);
        $this->assertTrue($this->stub->got('__isset', 'prop')->once());
    }
    
    public function test__unsetShouldRecord()
    {
        unset($this->stub->prop);
        $this->assertTrue($this->stub->got('__unset', 'prop')->once());
    }

    public function test__unsetShouldUnsetProp()
    {
        $this->stub->prop = 'value';
        unset($this->stub->prop);
        $this->assertFalse(isset($this->stub->prop));
    }
}

