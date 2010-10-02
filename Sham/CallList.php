<?php
namespace Sham;

require_once 'Sham/Matcher/Any.php';
require_once 'Sham/Matcher/ArrayMatcher.php';

use Sham\Call,
    Sham\Matcher\ArrayMatcher,
    Sham\Matcher\Any;

class CallList implements \Countable
{
    public $calls = array();

    public function __construct($calls = array())
    {
        $this->calls = (array) $calls;
    }

    public function add($spec, $params = array(), $return = null)
    {
        if (is_string($spec)) {
            $spec = new Call($spec, $params, $return);
        }

        $this->calls[] = $spec;
    }

    public function calls($name = null)
    {
        // don't filter at all?
        if (empty($name)) {
            return $this;
        }

        $args = func_get_args();
        array_shift($args); // take off call name
        if (empty($args)) {
            $argMatcher = new Any();
        } else {
            $argMatcher = new ArrayMatcher($args);
        }

        $calls = array();
        foreach ($this->calls as $call) {
            if ($call->name === $name && $argMatcher->matches($call->params)) {
                $calls[] = $call;
            }
        }
        return new CallList($calls);
    }

    public function once()
    {
        return $this->times(1);
    }
    
    public function never()
    {
        return $this->times(0);
    }

    public function times($count)
    {
        return count($this->calls) === $count;
    }

    public function first()
    {
        if (! count($this->calls)) {
            return false;
        }
        return $this->calls[0];
    }

    public function count()
    {
        return count($this->calls);
    }
}
