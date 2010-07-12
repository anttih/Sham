<?php
require_once 'Sham/Exception.php';
class Sham_Call
{
    public $name;
    public $params;
    public $return_value;

    public function __construct($name, array $params, $return_value)
    {
        $this->name = $name;
        $this->params = $params;
        $this->return_value = $return_value;
    }
}
