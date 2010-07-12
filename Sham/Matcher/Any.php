<?php
require_once 'Sham/Matcher.php';
class Sham_Matcher_Any implements Sham_Matcher
{
    public function matches($value)
    {
        return true;
    }
}