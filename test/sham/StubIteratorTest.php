<?php
require_once 'sham/Stub.php';

use sham\Stub;

class Sham_StubIteratorTest extends \PHPUnit_Framework_TestCase
{
    public $iteration_count;
    public $stub;
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

        $stub = new Stub();
        $stub->shamSetData($this->data);

        $i = 0;
        foreach ($stub as $key => $value) {
            $this->current[] = current($this->data);
            $this->valid[]   = current($this->data) !== false;
            $this->key[]     = key($this->data);
            $this->next[]    = next($this->data);
            $i++;
        }

        // iteration ends
        $this->valid[] = current($this->data) !== false;
        
        $this->iteration_count = $i;
        $this->stub = $stub;
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
        $this->assertTrue($this->stub->calls('key')->times(3));
    }

    public function testShouldRecordKeyReturnValue()
    {
        $this->_assertReturnValues('key');
    }

    public function testShouldRecordCurrent()
    {
        $this->assertTrue($this->stub->calls('current')->times(3));
    }

    public function testShouldRecordCurrentReturnValue()
    {
        $this->_assertReturnValues('current');
    }

    public function testShouldRecordNext()
    {
        $this->assertTrue($this->stub->calls('next')->times(3));
    }

    public function testShouldRecordNextReturnValue()
    {
        $this->_assertReturnValues('next');
    }

    public function testShouldRecordValid()
    {
        $this->assertTrue($this->stub->calls('valid')->times(4));
    }

    public function testShouldRecordValidReturnValue()
    {
        $this->_assertReturnValues('valid');
    }

    public function testShouldRecordRewind()
    {
        $this->assertTrue($this->stub->calls('rewind')->once());
    }

    public function testShouldResetWhenInterating()
    {
        $i = 0;
        foreach ($this->stub as $key => $value) {
            $i++;
        }
        $this->assertEquals(count($this->data), $i++);
    }

    private function _assertReturnValues($name)
    {
        $calls = $this->stub->calls($name)->calls;
        foreach ($this->$name as $key => $value) {
            $this->assertEquals($value, $calls[$key]->return_value);
        }
    }
}
