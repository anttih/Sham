<?php
namespace sham\matcher;

require_once 'sham/matcher/Matcher.php';

use sham\matcher\Matcher;

class Any implements Matcher
{
    public function matches($value)
    {
        return true;
    }
}