<?php
require_once 'Sham/Exception.php';
class Sham_MethodStub
{
    public $name;

    private $_return_value;
    private $_exception;

    public function __construct($name, $return_value = null)
    {
        $this->name = $name;
        $this->_return_value = $return_value;
    }

    public function returns($value)
    {
        $this->_return_value = $value;
    }

    public function throws($class = 'Sham_Exception')
    {
        $this->_exception = $class;
    }

    public function __invoke()
    {
        if ($this->_exception) {
            if (is_string($this->_exception)) {
                $class = $this->_exception;
                throw new $class();
            } else if ($this->_exception instanceof Exception) {
                throw $this->_exception;
            }
        }

        return $this->_return_value;
    }
}
