<?php
namespace Sham\Matcher;

require_once 'Sham/Matcher.php';

use Sham\Matcher;

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