<?php
namespace sham\Matcher;

require_once 'sham/Matcher/Matcher.php';

use sham\Matcher\Matcher;

class Any implements Matcher
{
    public function matches($value)
    {
        return true;
    }
}