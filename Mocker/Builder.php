<?php
class Mocker_Builder
{
    protected $_mock_class_name = 'Mocker_Mock_';

    private $_class;

    public function __construct()
    {
        $this->_mock_class_name = $this->_generateMockClassName();
    }

    public function build($class)
    {
        $code = $this->_buildMockCode($class);
        eval($code);
        $class = $this->_mock_class_name;
        return new $class();
    }

    private function _buildMockCode($class)
    {
        $lines = file(__dir__ . DIRECTORY_SEPARATOR . 'Mock.php');
        $this->_class = $class;
        $code = $this->_buildClassDefinition($lines);
        $code = $this->_buildMethods($code);
        return $code;
    }

    private function _buildClassDefinition($lines)
    {
        $reflection = new ReflectionClass('Mocker_Mock');
        $lines = array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - 1
        );

        $lines[0] = str_replace(
            'class Mocker_Mock',
            "class {$this->_mock_class_name} extends $this->_class",
            $lines[0]
        );

        return implode("\n", $lines);
    }

    private function _buildMethods($code)
    {
        $methods = array();
        $mocked = new ReflectionClass($this->_class);

        foreach ($mocked->getMethods() as $method) {
            $name = $method->getName();

            // ignore declared methods
            if ($this->_isDeclared($name)) {
                continue;
            }

            $call = "\$this->__call('$name', func_get_args());";
            $parameters = $this->_buildParameters($method);
            $func = "    public function $name($parameters) {\n        $call\n    }";
            $methods[] = $func;
        }

        $code = str_replace(
            '// :METHODS:',
            implode("\n", $methods),
            $code
        );

        return $code;
    }

    private function _buildParameters($method)
    {
        $params = array();
        foreach ($method->getParameters() as $param) {
            $params[] = $this->_buildParameter($param);
        }
        return implode(', ', $params);
    }

    private function _buildParameter($param)
    {
        $default = '';
        if ($param->isOptional()) {
            $value = $param->getDefaultValue();
            if (is_string($value)) {
                $value = "'$value'";
            } else if (is_null($value)) {
                $value = 'null';
            } else if (is_array($value)) {
                $value = 'array()';
            }

            $default = " = $value";
        }

        $class = '';
        if ($param->getClass()) {
            $class = $param->getClass()->getName() . ' ';
        }

        return $class . '$' . $param->getName() . $default;
    }

    private function _isDeclared($name)
    {
        static $methods = array();
        if (empty($methods)) {
            $class = new ReflectionClass('Mocker_Mock');
            $methods = array();
            foreach ($class->getMethods() as $method) {
                $methods[] = $method->getName();
            }
        }
        return in_array($name, $methods);
    }

    private function _generateMockClassName()
    {
        return 'Mocker_Mock_' . rand(1000, 9999);
    }
}
