<?php
require_once 'Mocker/Mock.php';

class MockTest extends PHPUnit_Framework_TestCase
{
    public function testCallingMethodsShouldReturnMocker_Mock()
    {
        $mocker = new Mocker_Mock();
        $ret = $mocker->method();
        $this->assertTrue($ret instanceof Mocker_Mock);
    }
    
    public function testAccessingPropertiesShouldReturnMocker_Mock()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue($mocker->property instanceof Mocker_Mock);
    }

    public function testShouldReturnSetPropertyValue()
    {
        $mocker = new Mocker_Mock();
        $mocker->key = "key value";
        $this->assertEquals("key value", $mocker->key);
    }

    public function testShouldCreateNewMocker_MocksWhenAccessed()
    {
        $mocker = new Mocker_Mock();
        $mocker->foo->bar = 'irrelevant';
        $this->assertTrue($mocker->foo instanceof Mocker_Mock);
        $this->assertTrue($mocker->foo !== $mocker);
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

    public function testCanInvokeMocker_Mock()
    {
        $mocker = new Mocker_Mock();
        $mocker();
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
        $this->assertTrue($mocker->calls->calls[0][2] instanceof Mocker_Mock);
    }

    public function testShouldProxyCallsToCallList()
    {
        $mocker = new Mocker_Mock();
        $this->assertTrue($mocker->calls() instanceof Mocker_CallList);
    }
}
