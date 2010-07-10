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
class Sham_Mock implements ArrayAccess
{
    /**
     * Internal value to distinguish falsy return
     * values from "no return value at all"
     * 
     * @param string
     */
    const NO_RETURN_VALUE = '30f5d20d150152d4413984f71fabd7d0';

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
    private $_calls = array();

    /**
     * The data this object will use for ArrayAccess and "struct"
     * behaviour (data object)
     * 
     * @param array
     */
    private $_data = array();

    /**
     * Return value of __invoke
     *
     * No return value set by default
     */
    private $_return_value = self::NO_RETURN_VALUE;

    public function __construct()
    {
        $this->_call_list = new Sham_CallList();
    }

    public function __destruct() {}

    public function __call($method, $params)
    {
        if (array_key_exists($method, $this->_calls)) {
            $call = $this->_calls[$method];
            $call->params = $params;
        } else {
            $call = new Sham_Call($method, $params, new Sham_Mock());
        }

        $this->_call_list->add($call);
        return $call();
    }

    public function __invoke()
    {
        $return = $this;
        if ($this->_return_value !== self::NO_RETURN_VALUE) {
            $return = $this->_return_value;
        }

        $this->_call_list->add('__invoke', func_get_args(), $return);
        return $return;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            $this->_call_list->add('__get', array($name), $this->_data[$name]);
            return $this->_data[$name];
        }

        $call = new Sham_Call($name, array(), self::NO_RETURN_VALUE);
        $this->_calls[$name] = $call;
        return $call;
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

    public function returns($value)
    {
        $this->_return_value = $value;
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

    public function __isset($name) {}

    public function __unset($name) {}

    public static function __callStatic($method, $params) {}

    public function __sleep() {}

    public function __wakeup() {}

    public function __toString() {}

    public static function __setState($properties = array()) {}

    public function __clone() {}

    // :METHODS:
}
