<?php
namespace sham;

require_once 'sham/matcher/ArrayMatcher.php';
require_once 'sham/Exception.php';

use sham\Sham,
    sham\Matcher\ArrayMatcher;

class Method
{
    public $name;

    private $_actions;
    
    private $_pending_matcher;

    public function __construct($name)
    {
        $this->name = $name;
        $this->_actions = array();
    }

    public function given(/*...*/)
    {
        $this->_pending_matcher = new ArrayMatcher(func_get_args());
        return $this;
    }

    public function returns($value)
    {
        $this->_implementation($value);
    }

    public function does($callback)
    {
        $this->_implementation(null, $callback);
    }
    
    private function _implementation($return_value, $callback = null)
    {
        $matcher = $this->_consumePendingMatcher();
        array_unshift($this->_actions, array($matcher, $return_value, $callback));
    }

    public function throws($exception = 'sham\Exception')
    {
        $this->does(function() use ($exception) {
            if (is_string($exception)) {
                throw new $exception();
            } else {
                throw $exception;
            }
        });
    }
    
    private function _consumePendingMatcher()
    {
        if ($this->_pending_matcher) {
            $matcher = $this->_pending_matcher;
            $this->_pending_matcher = null;
        } else {
            $matcher = Sham::any();
        }
        return $matcher;
    }

    public function __invoke()
    {
        $args = func_get_args();
        foreach ($this->_actions as $pair) {
            list($matcher, $ret, $callback) = $pair;
            if ($matcher->matches($args)) {
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $args);
                } else {
                    return $ret;
                }
            }
        }
        
        throw new \sham\Exception("Nothing stubbed for method '{$this->name}'.");
    }
}
