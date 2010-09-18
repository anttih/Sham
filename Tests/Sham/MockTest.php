<?php
require_once 'Sham/Mock.php';

class Sham_MockTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->mock = new Sham_Mock();
    }

    public function testCallingMethodsShouldReturnNewSham_MockByDefault()
    {
        $one = $this->mock->method();
        $two = $this->mock->method();
        $this->assertTrue($one instanceof Sham_Mock);
        $this->assertNotSame($one, $two);
    }
    
    public function testCallingMethodsAfterGetShouldReturnNewSham_MockByDefault()
    {
        $this->mock->method;
        $one = $this->mock->method();
        $two = $this->mock->method();
        $this->assertTrue($one instanceof Sham_Mock);
        $this->assertNotSame($one, $two);
    }
    
    public function testCallingMethodsShouldSendParameters()
    {
        $this->mock->method->given(1)->returns(2);
        $this->assertEquals(2, $this->mock->method(1));
    }
    
    public function testGettingAPropertyWithoutSettingShouldReturnMethodStubObject()
    {
        $this->assertTrue($this->mock->property instanceof Sham_MethodStub);
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

    public function testShouldReturnFromChildSham_Mock()
    {
        $this->mock->method->returns("return value");
        $this->assertEquals("return value", $this->mock->method());
    }

    public function testShouldRecordInvoke()
    {
        $mock = new Sham_Mock();
        $mock();
        $this->assertTrue($mock->calls('__invoke')->once());
    }

    public function testShouldRecordInvokeParams()
    {
        $mock = new Sham_Mock();
        $mock('param 1');
        $this->assertTrue($mock->calls('__invoke', 'param 1')->once());
    }

    public function testInvokeShouldReturnNewMockWhenNoReturnValue()
    {
        $mock = new Sham_Mock();
        $this->assertTrue($mock() instanceof Sham_Mock);
        $this->assertTrue($mock() !== $mock);
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $mock = new Sham_Mock();
        $mock->returns('return value');
        $this->assertSame('return value', $mock());
    }
    
    public function testShouldReturnSameMethodStubOnEachRequest()
    {
        $mock = new Sham_Mock();
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
        $this->assertTrue($this->mock->calls() instanceof Sham_CallList);
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

    public function testShouldRecordIsset()
    {
        $this->mock->shamSetData(array(1, 2));
        isset($this->mock[0]);
        $this->assertTrue($this->mock->calls('offsetExists', 0)->once());
    }

    public function testShouldIssetReturnValueAsCallReturnValue()
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
        $mock = new Sham_Mock();
        $this->assertEquals(get_class($mock), (string) $mock);
    }

    public function testCanSetToStringReturnValue()
    {
        $mock = new Sham_Mock();
        $mock->__toString->returns('to string');
        $this->assertEquals('to string', (string) $mock);
    }

}

