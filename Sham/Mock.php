<?php

require_once 'Sham/CallList.php';
require_once 'Sham/Call.php';
require_once 'Sham/MethodStub.php';

/**
 * Test Stub/Spy object that records everything you do on it
 *
 * This class is also a template for mocking existing classes.
 * 
 * @package Sham
 */
class Sham_Mock implements ArrayAccess, Iterator
{
    /**
     * Calls that have been recorded
     * 
     * @param Sham_CallList
     */
    private $_calls;
    
    /**
     * Calls that have not been recorded yet
     *
     * When you set a return value for a call,
     * that call will be stored here until it is
     * actually called.
     * 
     * @param array
     */
    private $_method_stubs = array();

    /**
     * The data this object will use for ArrayAccess and "struct"
     * behaviour (data object)
     * 
     * @param array
     */
    private $_data = array();

    public function __construct()
    {
        $this->_calls = new Sham_CallList();
        
        $str = get_class($this);
        $this->_method_stubs['__toString'] = new Sham_MethodStub('__toString');
        $this->_method_stubs['__toString']->returns($str);
    }

    public function __destruct() {}

    public function __call($method, /*:__call1_array:*/ $params = array())
    {
        if (array_key_exists($method, $this->_method_stubs)) {
            $stub = $this->_method_stubs[$method];
            $ret = call_user_func_array($stub, $params);
        } else {
            $ret = new Sham_Mock();
        }
        
        $this->_calls->add(new Sham_Call($method, $params, $ret));
        
        return $ret;
    }

    public function __invoke()
    {
        return $this->__call('__invoke', func_get_args());
    }

    public function returns($value)
    {
        $this->__invoke->returns($value);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            $this->_calls->add('__get', array($name), $this->_data[$name]);
            return $this->_data[$name];
        }
        return $this->_getMethodStub($name);
    }

    private function _getMethodStub($name)
    {
        if (! isset($this->_method_stubs[$name])) {
            $stub = new Sham_MethodStub($name);
            $stub->does(function() {
                return new Sham_Mock();
            });
            $this->_method_stubs[$name] = $stub;
        }
        return $this->_method_stubs[$name];
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
        $this->_calls->add('__set', array($value));
    }

    public function calls()
    {
        return call_user_func_array(
            array($this->_calls, 'calls'),
            func_get_args()
        );
    }

    public function shamGetData()
    {
        return $this->_data;
    }

    public function shamSetData($data)
    {
        $this->_data = $data;
    }

    public function offsetSet($offset, $value) {
        $this->_data[$offset] = $value;
        $this->_calls->add('offsetSet', array($offset, $value));
    }

    public function offsetExists($offset) {
        $isset = isset($this->_data[$offset]);
        $this->_calls->add('offsetExists', array($offset), $isset);
        return $isset;
    }

    public function offsetUnset($offset) {
        $this->_calls->add('offsetUnset', array($offset));
    }

    public function offsetGet($offset) {
        $this->_calls->add('offsetGet', array($offset), $this->_data[$offset]);
        return $this->_data[$offset];
    }

    // BEGIN ITERATOR

    public function rewind() {
        reset($this->_data);
        $this->_calls->add('rewind');
    }

    public function current() {
        $value = current($this->_data);
        $this->_calls->add('current', array(), $value);
        return $value;
    }

    public function key() {
        $key = key($this->_data);
        $this->_calls->add('key', array(), $key);
        return $key;
    }

    public function next() {
        $value = next($this->_data);
        $this->_calls->add('next', array(), $value);
        return $value;
    }

    public function valid() {
        $valid = current($this->_data) !== false;
        $this->_calls->add('valid', array(), $valid);
        return $valid;
    }
    
    // END ITERATOR

    public function __isset($name) {}
    public function __unset($name) {}

    // serialize/unserialize
    public function __sleep() {}
    public function __wakeup() {}

    public function __toString() {
        return $this->__call('__toString');
    }

    public function __clone() {}

    public static function __setState(/*:__setState0_array:*/ $properties = array()) {}
    public static function __callStatic($method, /*:__callStatic1_array:*/ $params) {}

    // :METHODS:
}
