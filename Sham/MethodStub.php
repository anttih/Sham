<?php
require_once 'Sham/Matcher/Array.php';
require_once 'Sham/Exception.php';
class Sham_MethodStub
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
        $this->_pending_matcher = new Sham_Matcher_Array(func_get_args());
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

    public function throws($exception = 'Sham_Exception')
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
        
        throw new Sham_Exception("Nothing stubbed for method '{$this->name}'.");
    }
}
