<?php
namespace sham;

use \ReflectionClass;

class Builder
{
    protected static $_next_mock_id = 1001;

    protected $_mock_class_name = 'Mock_';

    private $_class;
    
    private $_removed_declared_methods = array();

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
        $this->_class = new ReflectionClass($class);
        $code = $this->_buildClassDefinition($lines);
        if ($this->_iteratorImplementationShouldBeRemoved()) {
            $code = $this->_removeIteratorImplementation($code);
        }
        $code = $this->_buildMethods($code);
        $code = $this->_adjustMagicMethodSignatures($code);
        return $code;
    }

    private function _buildClassDefinition($lines)
    {
        $reflection = new ReflectionClass('sham\Mock');
        $lines = array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - 1
        );

        $name = $this->_class->getName();
        $def = $lines[0];
        if ($this->_class->isInterface()) {
            $def = str_replace(
                'class Mock',
                "class {$this->_mock_class_name}",
                $def
            );
            $def .= ", $name";
        } else {
            $def = str_replace(
                'class Mock',
                "class {$this->_mock_class_name} extends $name",
                $def
            );
        }
        
        $lines[0] = $def;
        return implode('', $lines);
    }
    
    private function _buildMethods($code)
    {
        $methods = array();
        foreach ($this->_class->getMethods() as $method) {
            $name = $method->getName();

            // don't implement these
            if ($method->isStatic()
            || $method->isFinal()
            || $this->_isDeclared($name)
            || $this->_isNotAbstractNonPublic($method)) {
                continue;
            }

            $visibility = $this->_getVisibility($method);
            $call = "\$this->__call('$name', func_get_args());";
            $parameters = $this->_buildParameters($method);
            $func = "    $visibility function $name($parameters) {\n        return $call\n    }";
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
            $default = ' = ' . var_export($param->getDefaultValue(), true);
        }

        $typehint = '';
        if ($param->getClass()) {
            $typehint = $param->getClass()->getName() . ' ';
        } elseif ($param->isArray()) {
            $typehint = 'array ';
        }

        return $typehint . '$' . $param->getName() . $default;
    }
    
    private function _adjustMagicMethodSignatures($code)
    {
        $code = $this->_preserveArrayTypehint($code, '__call', 1);
        $code = $this->_preserveArrayTypehint($code, '__callStatic', 1);
        $code = $this->_preserveArrayTypehint($code, '__setState', 0);
        return $code;
    }
    
    private function _preserveArrayTypehint($code, $method, $param_index)
    {
        if ($this->_class->hasMethod($method)) {
            $refl = $this->_class->getMethod($method);
            $params = $refl->getParameters();
            if ($params[$param_index]->isArray()) {
                $placeholder = "/*:{$method}{$param_index}_array:*/";
                $code = str_replace($placeholder, 'array', $code);
            }
        }
        return $code;
    }
    
    private function _iteratorImplementationShouldBeRemoved()
    {
        return $this->_class->implementsInterface('IteratorAggregate') ||
               (!$this->_class->implementsInterface('Iterator') && $this->_classHasIteratorMethods());
    }
    
    private function _removeIteratorImplementation($code)
    {
        $code = $this->_withoutImplementedInterface($code, 'Iterator');
        $code = preg_replace('_// BEGIN ITERATOR.*// END ITERATOR_ms', '', $code);
        foreach ($this->_iteratorMethods() as $method) {
            $this->_removed_declared_methods[] = $method;
        }
        return $code;
    }
    
    private function _withoutImplementedInterface($code, $interface)
    {
        $matches = array();
        return preg_replace_callback(
            '/(class .* implements )(.*)\n/m',
            function($matches) use ($interface) {
                $interfaces = array_map('trim', explode(',', $matches[2]));
                $interfaces = array_diff($interfaces, array('\\' . $interface));
                return $matches[1] . implode(', ', $interfaces);
            },
            $code
        );
    }
    
    private function _classHasIteratorMethods()
    {
        foreach ($this->_iteratorMethods() as $method) {
            if ($this->_class->hasMethod($method)) {
                return true;
            }
        }
        return false;
    }
    
    private function _iteratorMethods()
    {
        return array('current', 'key', 'next', 'rewind', 'valid');
    }

    private function _getVisibility($method)
    {
        if ($method->isPublic()) {
            return 'public';
        } else if ($method->isProtected()) {
            return 'protected';
        } else if ($method->isPrivate()) {
            return 'private';
        }
    }

    private function _isNotAbstractNonPublic($method)
    {
        $visibility = $this->_getVisibility($method);
        $non_public = array('protected', 'private');
        return (! $method->isAbstract()) && (in_array($visibility, $non_public));
    }

    private function _isDeclared($name)
    {
        static $methods = array();
        if (empty($methods)) {
            $class = new ReflectionClass('sham\Mock');
            $methods = array();
            foreach ($class->getMethods() as $method) {
                $methods[] = $method->getName();
            }
        }
        return in_array($name, array_diff($methods, $this->_removed_declared_methods));
    }

    private function _generateMockClassName()
    {
        return $this->_mock_class_name . self::$_next_mock_id++;
    }
}
