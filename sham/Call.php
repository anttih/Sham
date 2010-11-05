<?php
namespace sham;

require_once 'sham/Exception.php';
class Call
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
