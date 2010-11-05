<?php
namespace sham\Matcher;

require_once 'sham/Matcher.php';

use sham\Matcher;

class Any implements Matcher
{
    public function matches($value)
    {
        return true;
    }
}