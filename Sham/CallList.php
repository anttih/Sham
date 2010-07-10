<?php
require_once 'Sham.php';
class Sham_CallList implements Countable
{
    public $calls = array();

    public function __construct($calls = array())
    {
        $this->calls = (array) $calls;
    }

    public function add($spec, $params = array(), $return = null)
    {
        if (is_string($spec)) {
            // ignore optional arguments that were not passed in
            $params = array_filter($params, function ($value) {
                return $value !== Sham::NO_VALUE_PASSED;
            });
            $spec = new Sham_Call($spec, $params, $return);
        }

        $this->calls[] = $spec;
    }

    public function calls($name = null)
    {
        // don't filter at all?
        if (empty($name)) {
            return $this;
        }

        $calls = array();
        foreach ($this->calls as $call) {
            $args = func_get_args();
            // take off call name
            array_shift($args);
            if ($call->name === $name && $this->_emptyOrEqualArgs($args, $call->params)) {
                $calls[] = $call;
            }
        }
        return new Sham_CallList($calls);
    }

    public function once()
    {
        return $this->times(1);
    }

    public function times($count)
    {
        return count($this->calls) === $count;
    }

    private function _emptyOrEqualArgs($args, $call_args)
    {
        $arg_count = count($args);
        if ($arg_count == 0) {
            return true;
        } else if ($arg_count != count($call_args)) {
            return false;
        }

        return $this->_equalArgs($args, $call_args);
    }

    private function _equalArgs($args, $call_args)
    {
        for ($i = 0; $i < count($args); $i++) {
            $allowed = array($call_args[$i], Sham::DONTCARE);
            if (! in_array($args[$i], $allowed)) {
                return false;
            }
        }
        return true;
    }

    public function count()
    {
        return count($this->calls);
    }
}
