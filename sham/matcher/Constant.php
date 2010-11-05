<?php
namespace sham\matcher;

require_once 'sham/matcher/Matcher.php';

use sham\matcher\Matcher;

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