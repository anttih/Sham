<?php
require_once 'Mocker.php';
class Mocker_CallList implements Countable
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
                return $value !== Mocker::NO_VALUE_PASSED;
            });
            $spec = new Mocker_Call($spec, $params, $return);
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
            if ($call->name === $name && $this->_emptyOrEqualArgs($args, $call->params)) {
                $calls[] = $call;
            }
        }
        return new Mocker_CallList($calls);
    }

    public function once()
    {
        return count($this->calls) == 1;
    }

    private function _emptyOrEqualArgs($args, $call_args)
    {
        array_shift($args);

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
            $allowed = array($call_args[$i], Mocker::DONTCARE);
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
