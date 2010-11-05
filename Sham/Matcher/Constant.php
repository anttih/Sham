<?php
namespace sham\Matcher;

require_once 'sham/Matcher.php';

use sham\Matcher;

class Constant implements Matcher
{
    private $_value;

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function matches($value)
    {
        return $this->_value === $value;
    }
}