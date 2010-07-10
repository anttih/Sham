<?php
class Sham_Call
{
    /**
     * Internal value to distinguish falsy return
     * values from "no return value at all"
     * 
     * @param string
     */
    const NO_RETURN_VALUE = '30f5d20d150152d4413984f71fabd7d0';

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

    public function returns($value = self::NO_RETURN_VALUE)
    {
        if ($value === self::NO_RETURN_VALUE) {
            return $this->return_value;
        }
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
