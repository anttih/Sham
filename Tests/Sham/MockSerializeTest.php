<?php
require_once 'Sham/Mock.php';

use Sham\Mock;

class Sham_MockSerializeTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $stub = new Mock();
        $stub->shamSetData(array('prop' => 'value'));
        $stub->method->given('param')->returns('return value');

        $serialized = serialize($stub);
        $this->zombie = unserialize($serialized);
    }

    public function testShouldRecord__sleep()
    {
        $this->assertTrue($this->zombie->calls('__sleep')->once());
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
        $this->assertTrue($this->zombie->calls('__wakeup')->once());
    }

}
