<?php

require_once 'Sham/CallList.php';
require_once 'Sham/Call.php';

/**
 * Mock/stub object that records everything you do on it
 *
 * This class is also a template for mocking existing classes.
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * @package Sham
 */
class Sham_Mock implements ArrayAccess, Iterator
{
    /**
     * Calls that have been recorded
     * 
     * @param Sham_CallList
     */
    private $_call_list;
    
    /**
     * Calls that have not been recorded yet
     *
     * When you set a return value for a call,
     * that call will be stored here until it is
     * actually called.
     * 
     * @param array
     */
    private $_methodStubs = array();

    /**
     * The data this object will use for ArrayAccess and "struct"
     * behaviour (data object)
     * 
     * @param array
     */
    private $_data = array();

    public function __construct()
    {
        $this->_call_list = new Sham_CallList();
        $str = get_class($this);
        $this->_methodStubs['__toString'] = new Sham_MethodStub('__toString', $str);
    }

    public function __destruct() {}

    public function __call($method, /*:__call1_array:*/ $params = array())
    {
        if (array_key_exists($method, $this->_methodStubs)) {
            $stub = $this->_methodStubs[$method];
            $ret = $stub();
        } else {
            $ret = new Sham_Mock();
        }
        
        $this->_call_list->add(new Sham_Call($method, $params, $ret));
        
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
            $this->_call_list->add('__get', array($name), $this->_data[$name]);
            return $this->_data[$name];
        }

        $stub = new Sham_MethodStub($name);
        $this->_methodStubs[$name] = $stub;
        return $stub;
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
        $this->_call_list->add('__set', array($value));
    }

    public function calls()
    {
        return call_user_func_array(
            array($this->_call_list, 'calls'),
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
        $this->_call_list->add('offsetSet', array($offset, $value));
    }

    public function offsetExists($offset) {
        $isset = isset($this->_data[$offset]);
        $this->_call_list->add('offsetExists', array($offset), $isset);
        return $isset;
    }

    public function offsetUnset($offset) {
        $this->_call_list->add('offsetUnset', array($offset));
    }

    public function offsetGet($offset) {
        $this->_call_list->add('offsetGet', array($offset), $this->_data[$offset]);
        return $this->_data[$offset];
    }

    // Iterator

    public function rewind() {
        reset($this->_data);
        $this->_call_list->add('rewind');
    }

    public function current() {
        $value = current($this->_data);
        $this->_call_list->add('current', array(), $value);
        return $value;
    }

    public function key() {
        $key = key($this->_data);
        $this->_call_list->add('key', array(), $key);
        return $key;
    }

    public function next() {
        $value = next($this->_data);
        $this->_call_list->add('next', array(), $value);
        return $value;
    }

    public function valid() {
        $valid = current($this->_data) !== false;
        $this->_call_list->add('valid', array(), $valid);
        return $valid;
    }
    

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
