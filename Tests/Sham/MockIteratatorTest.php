<?php
require_once 'sham/Mock.php';

use sham\Mock;

class Sham_MockIteratorTest extends \PHPUnit_Framework_TestCase
{
    public $iteration_count;
    public $mock;
    public $next = array();
    public $current = array();
    public $valid = array();
    public $key = array();

    public function setup()
    {
        $this->data = array(
            'value 1',
            'key' => 'value 2',
            2     => 'value 3'
        );

        $mock = new Mock();
        $mock->shamSetData($this->data);

        $i = 0;
        foreach ($mock as $key => $value) {
            $this->current[] = current($this->data);
            $this->valid[]   = current($this->data) !== false;
            $this->key[]     = key($this->data);
            $this->next[]    = next($this->data);
            $i++;
        }

        // iteration ends
        $this->valid[] = current($this->data) !== false;
        
        $this->iteration_count = $i;
        $this->mock = $mock;
    }

    public function testShouldIterateAllValues()
    {
        $this->assertEquals($this->iteration_count, count($this->data));
    }

    public function testShouldGetCorrectKeys()
    {
        $this->assertSame($this->key, array_keys($this->data));
    }

    public function testShouldRecordKey()
    {
        $this->assertTrue($this->mock->calls('key')->times(3));
    }

    public function testShouldRecordKeyReturnValue()
    {
        $this->_assertReturnValues('key');
    }

    public function testShouldRecordCurrent()
    {
        $this->assertTrue($this->mock->calls('current')->times(3));
    }

    public function testShouldRecordCurrentReturnValue()
    {
        $this->_assertReturnValues('current');
    }

    public function testShouldRecordNext()
    {
        $this->assertTrue($this->mock->calls('next')->times(3));
    }

    public function testShouldRecordNextReturnValue()
    {
        $this->_assertReturnValues('next');
    }

    public function testShouldRecordValid()
    {
        $this->assertTrue($this->mock->calls('valid')->times(4));
    }

    public function testShouldRecordValidReturnValue()
    {
        $this->_assertReturnValues('valid');
    }

    public function testShouldRecordRewind()
    {
        $this->assertTrue($this->mock->calls('rewind')->once());
    }

    public function testShouldResetWhenInterating()
    {
        $i = 0;
        foreach ($this->mock as $key => $value) {
            $i++;
        }
        $this->assertEquals(count($this->data), $i++);
    }

    private function _assertReturnValues($name)
    {
        $calls = $this->mock->calls($name)->calls;
        foreach ($this->$name as $key => $value) {
            $this->assertEquals($value, $calls[$key]->return_value);
        }
    }
}
