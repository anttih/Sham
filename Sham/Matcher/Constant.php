<?php
require_once 'Sham/Matcher.php';
class Sham_Matcher_Constant implements Sham_Matcher
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