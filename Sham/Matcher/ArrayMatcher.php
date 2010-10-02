<?php
namespace Sham\Matcher;

require_once 'Sham/Matcher.php';

use Sham\Matcher;

class ArrayMatcher implements Matcher
{
    private $_expected;

    public function __construct(array $expected)
    {
        $this->_expected = $expected;
    }

    public function matches($value)
    {
        if (is_array($value) && count($value) == count($this->_expected)) {
            return $this->_eachElementMatches($value);
        } else {
            return false;
        }
    }
    
    private function _eachElementMatches($target)
    {
        foreach ($this->_expected as $k => $v) {
            if (!array_key_exists($k, $target)) {
                return false;
            } elseif ($v instanceof Matcher) {
                if (!$v->matches($target[$k])) {
                    return false;
                }
            } elseif ($v !== $target[$k]) {
                return false;
            }
        }
        
        return true;
    }
}