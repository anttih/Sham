<?php
require_once 'Mocker.php';

class MockerTest extends PHPUnit_Framework_TestCase
{
    public function testCallingMethodsShouldReturnMocker()
    {
        $mocker = new Mocker();
        $ret = $mocker->method();
        $this->assertTrue($ret instanceof Mocker);
    }
    
    public function testAccessingPropertiesShouldReturnMocker()
    {
        $mocker = new Mocker();
        $this->assertTrue($mocker->property instanceof Mocker);
    }

    public function testShouldReturnSetPropertyValue()
    {
        $mocker = new Mocker();
        $mocker->key = "key value";
        $this->assertEquals("key value", $mocker->key);
    }

    public function testShouldCreateNewMockersWhenAccessed()
    {
        $mocker = new Mocker();
        $mocker->foo->bar = 'irrelevant';
        $this->assertTrue($mocker->foo instanceof Mocker);
        $this->assertTrue($mocker->foo !== $mocker);
    }

    public function testShouldReturnFromChildMocker()
    {
        $mocker = new Mocker();
        $mocker->method->setReturn("return value");
        $this->assertEquals("return value", $mocker->method());
    }

    public function testShouldAllowFalsyReturnValues()
    {
        $mocker = new Mocker();
        $mocker->someMethod->setReturn(false);
        $this->assertEquals(false, $mocker->someMethod());
    }

    public function testShouldBeAbleToGetArrayElements()
    {
        $mocker = new Mocker();
        $this->assertTrue($mocker['irrelevant'] instanceof Mocker);
    }

    public function testShouldReturnTrueForIsset()
    {
        $mocker = new Mocker();
        $this->assertTrue(isset($mocker['irrelevant']));
    }

    public function testCanInvokeMocker()
    {
        $mocker = new Mocker();
        $mocker();
    }

    public function testShouldReturnThisMockerIfNoReturnValueSet()
    {
        $mocker = new Mocker();
        $this->assertSame($mocker, $mocker());
    }

    public function testShouldUseReturnValueWhenInvoked()
    {
        $mocker = new Mocker();
        $mocker->setReturn('return value');
        $this->assertSame('return value', $mocker());
    }

    public function testShouldRecordCalls()
    {
        $mocker = new Mocker();
        $mocker->method0();
        $mocker->method1();
        $this->assertEquals(2, count($mocker->calls));
    }

    public function testShouldRecordReturnValue()
    {
        $mocker = new Mocker();
        $mocker->method();
        $this->assertTrue($mocker->calls->calls[0][2] instanceof Mocker);
    }
}
