<?php
class Mocker_Call
{
    public $name;
    public $params;
    public $return_value;

    private $_exception_class;

    public function __construct($name, $params = array(), $return_value = null)
    {
        $this->name = $name;
        $this->params = $params;
        $this->return_value = $return_value;
    }

    public function returns($value)
    {
        $this->return_value = $value;
    }

    public function throws($class)
    {
        $this->_exception_class = $class;
    }

    public function __invoke()
    {
        if ($this->_exception_class) {
            $class = $this->_exception_class;
            throw new $class();
        }

        return $this->return_value;
    }
}
