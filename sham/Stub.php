<?php
namespace sham;

require_once 'sham/CallList.php';
require_once 'sham/Call.php';
require_once 'sham/Method.php';

/**
 * Test Stub/Spy object that records everything you do on it
 *
 * This class is also a template for stubing existing classes.
 * 
 * @package Sham
 */
class Stub implements \ArrayAccess, \Iterator
{
    /**
     * Calls that have been recorded
     * 
     * @param sham\CallList
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
    private $_sham_data = array();

    public function __construct()
    {
        $this->_calls = new \sham\CallList();
        
        $str = get_class($this);
        $this->_method_stubs['__toString'] = new \sham\Method('__toString');
        $this->_method_stubs['__toString']->returns($str);
    }

    public function __destruct() {}

    public function __call($method, /*:__call1_array:*/ $params = array())
    {
        $stub = $this->_getMethod($method);
        $ret = call_user_func_array($stub, $params);
        
        $this->_calls->add(new \sham\Call($method, $params, $ret));
        
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
        if (array_key_exists($name, $this->_sham_data)) {
            $this->_calls->add('__get', array($name), $this->_sham_data[$name]);
            return $this->_sham_data[$name];
        }
        return $this->_getMethod($name);
    }

    private function _getMethod($name)
    {
        if (! isset($this->_method_stubs[$name])) {
            $stub = new \sham\Method($name);
            $stub->returns(new \sham\Stub());
            $this->_method_stubs[$name] = $stub;
        }
        return $this->_method_stubs[$name];
    }

    public function __set($key, $value)
    {
        $this->_sham_data[$key] = $value;
        $this->_calls->add('__set', array($value));
    }

    public function got()
    {
        return call_user_func_array(
            array($this->_calls, 'filter'),
            func_get_args()
        );
    }

    public function shamGetData()
    {
        return $this->_sham_data;
    }

    public function shamSetData($data)
    {
        $this->_sham_data = $data;
    }

    public function offsetSet($offset, $value) {
        $this->_sham_data[$offset] = $value;
        $this->_calls->add('offsetSet', array($offset, $value));
    }

    public function offsetExists($offset) {
        $isset = isset($this->_sham_data[$offset]);
        $this->_calls->add('offsetExists', array($offset), $isset);
        return $isset;
    }

    public function offsetUnset($offset) {
        $this->_calls->add('offsetUnset', array($offset));
    }

    public function offsetGet($offset) {
        $this->_calls->add('offsetGet', array($offset), $this->_sham_data[$offset]);
        return $this->_sham_data[$offset];
    }

    // BEGIN ITERATOR

    public function rewind() {
        reset($this->_sham_data);
        $this->_calls->add('rewind');
    }

    public function current() {
        $value = current($this->_sham_data);
        $this->_calls->add('current', array(), $value);
        return $value;
    }

    public function key() {
        $key = key($this->_sham_data);
        $this->_calls->add('key', array(), $key);
        return $key;
    }

    public function next() {
        $value = next($this->_sham_data);
        $this->_calls->add('next', array(), $value);
        return $value;
    }

    public function valid() {
        $valid = current($this->_sham_data) !== false;
        $this->_calls->add('valid', array(), $valid);
        return $valid;
    }
    
    // END ITERATOR

    public function __isset($name)
    {
        $ret = isset($this->_sham_data[$name]);
        $this->_calls->add('__isset', array($name), $ret);
        return $ret;
    }

    public function __unset($name)
    {
        unset($this->_sham_data[$name]);
        $this->_calls->add('__unset', array($name));
    }

    // serialize/unserialize
    public function __sleep()
    {
        $this->_calls->add('__sleep', array());
        return array('_calls', '_sham_data', '_method_stubs');
    }

    public function __wakeup()
    {
        $this->_calls->add('__wakeup');
    }

    public function __toString() {
        return $this->__call('__toString');
    }

    public function __clone() {}

    public static function __setState(/*:__setState0_array:*/ $properties = array()) {}
    public static function __callStatic($method, /*:__callStatic1_array:*/ $params) {}

    // :METHODS:
}
