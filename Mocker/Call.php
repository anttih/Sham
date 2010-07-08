<?php
class Mocker_Call
{
    public $name;
    public $params;
    public $return_value;

    public function __construct($name, $params = array(), $return_value = null)
    {
        $this->name = $name;
        $this->params = $params;
        $this->return_value = $return_value;
    }

    public function setReturn($value)
    {
        $this->return_value = $value;
    }

    public function __invoke()
    {
        return $this->return_value;
    }
}
