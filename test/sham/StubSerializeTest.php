<?php
require_once 'sham/Stub.php';

use sham\Stub;

class Sham_StubSerializeTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $stub = new Stub();
        $stub->shamSetData(array('prop' => 'value'));
        $stub->method->given('param')->returns('return value');

        $serialized = serialize($stub);
        $this->zombie = unserialize($serialized);
    }

    public function testShouldRecord__sleep()
    {
        $this->assertTrue($this->zombie->got('__sleep')->once());
    }

    public function testShouldPreserveShamData()
    {
        $this->assertEquals('value', $this->zombie->prop);
    }

    public function testShouldPreserveStubReturnValues()
    {
        $this->assertEquals('return value', $this->zombie->method('param'));
    }

    public function testShouldRecord__wakeup()
    {
        $this->assertTrue($this->zombie->got('__wakeup')->once());
    }

}
