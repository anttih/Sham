<?php
require_once 'Mocker/CallList.php';
class Mocker_Mock implements ArrayAccess
{
    const NO_RETURN_VALUE = '30f5d20d150152d4413984f71fabd7d0';

    public $calls;

    private $_data = array();

    /**
     * Value that should be returned
     *
     * No return value set by default
     */
    private $_return_value = self::NO_RETURN_VALUE;

    public function __construct()
    {
        $this->calls = new Mocker_CallList();
    }

    public function __call($method, $params)
    {
        // if $method has been accessed as a property
        // it is most likely a Mocker, so we'll invoke it
        // to use it's return value
        if (array_key_exists($method, $this->_data)
        && $this->_data[$method] instanceof Mocker_Mock) {
            return $this->$method->__invoke($params);
        }

        // first access, return a new Mocker
        $return = new Mocker_Mock();

        $this->calls->add($method, $params, $return);

        return $return;
    }

    public function __invoke()
    {
        if ($this->_return_value !== self::NO_RETURN_VALUE) {
            $return = $this->_return_value;
        } else {
            $return = $this;
        }
        return $return;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        $value = new Mocker_Mock();
        $this->_data[$name] = $value;
        return $value;
    }

    public function __isset($name) {}
    public function __unset($name) {}
    public static function __callStatic($method, $params) {}

    public function setReturn($value)
    {
        $this->_return_value = $value;
    }

    public function offsetSet($offset, $value) {
        return true;
    }

    public function offsetExists($offset) {
        return true;
    }

    public function offsetUnset($offset) {
    }

    public function offsetGet($offset) {
        return $this;
    }

    // :METHODS:
}
