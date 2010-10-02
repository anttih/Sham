<?php
namespace Sham\Matcher;

require_once 'Sham/Matcher.php';

use Sham\Matcher;

class Any implements Matcher
{
    public function matches($value)
    {
        return true;
    }
}