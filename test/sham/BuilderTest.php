<?php
require_once 'sham/Sham.php';
require_once 'sham/Builder.php';

use sham\Builder,
    \ReflectionClass;

class BuilderTest extends PHPUnit_Framework_TestCase
{
    public function testStubingAClassShouldReturnInstanceOfSameClass()
    {
        $builder = new Builder();
        $this->assertTrue($builder->build('TestBuilder') instanceof TestBuilder);
    }

    public function testOverrideParentMethods()
    {
        $builder = new Builder();
        $obj = $builder->build('TestBuilder');
        $this->assertHasOwnMethod($obj, 'override');
    }

    public function testRecordOverridenMethodCalls()
    {
        $builder = new Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override();
        $this->assertTrue($obj->got('override')->once());
    }
    
    public function testShouldReturnStubbedReturnValues()
    {
        $builder = new Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override->returns('foo');
        $this->assertEquals('foo', $obj->override());
    }

    public function testRecordCallParams()
    {
        $builder = new Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override('param 1');
        $this->assertTrue($obj->got('override', 'param 1')->once());
    }
    
    public function testRecordOptionalParamsWhenGiven()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithClassTypeHintOnOptionalParam');
        $param = new ClassWithParams();
        $obj->method(null);
        $obj->method($param);
        $this->assertTrue($obj->got('method', null)->once());
        $this->assertTrue($obj->got('method', $param)->once());
    }
    
    public function testDontRecordOptionalParamsWhenNotGiven()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithClassTypeHintOnOptionalParam');
        $obj->method();
        $this->assertEquals(array(), $obj->got('method')->first()->params);
    }

    public function testBuildMethodWithOneNonOptionalParam()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithParams');
        $this->assertNumberOfParameters(1, $obj, 'method');
    }

    public function testBuildMethodWithTwoNonOptionalParams()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithTwoParams');
        $this->assertNumberOfParameters(2, $obj, 'method');
    }

    public function testBuildMethodWithOneOptionalParam()
    {
        $params = $this->_getBuiltParams('ClassWithOptionalParam');
        $this->assertTrue($params[0]->isOptional());
    }

    public function testShouldUseSameDefaultParamValue()
    {
        $params = $this->_getBuiltParams('ClassWithOptionalParam');
        $this->assertEquals('thedefault', $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithClassTypeHint()
    {
        $params = $this->_getBuiltParams('ClassWithClassTypeHint');
        $this->assertEquals('ClassWithParams', $params[0]->getClass()->getName());
    }
    
    public function testBuildMethodWithArrayTypeHint()
    {
        $params = $this->_getBuiltParams('ClassWithArrayTypeHint');
        $this->assertTrue($params[0]->isArray());
    }
    
    public function testBuildMethodWithClassTypeHintOnOptionalParam()
    {
        $params = $this->_getBuiltParams('ClassWithClassTypeHintOnOptionalParam');
        $this->assertEquals('ClassWithParams', $params[0]->getClass()->getName());
        $this->assertEquals(null, $params[0]->getDefaultValue());
    }
    
    public function testBuildMethodWithArrayTypeHintOnOptionalParam()
    {
        $params = $this->_getBuiltParams('ClassWithArrayTypeHintOnOptionalParam');
        $this->assertTrue($params[0]->isArray());
        $this->assertEquals(array(1, 2, 3), $params[0]->getDefaultValue());
    }

    /**
     * This test will just die unless everything is ok
     */
    public function testDontAdd__call()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithMethodsDeclaredInStub');
    }
    
    public function testShouldPreserveArrayTypehintInInheritedMagicMethods()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithArrayTypehintedMagicMethods');
        
        $this->assertParamHasArrayTypehint($obj, '__call', 1);
        $this->assertParamHasArrayTypehint($obj, '__callStatic', 1);
        $this->assertParamHasArrayTypehint($obj, '__setState', 0);
    }
    
    public function testShouldIgnoreStaticMethods()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithStaticMethod');
        // Fails with a fatal error if builder attempts to override with a non-static.
    }

    public function testBuildAbstractClass()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithAbstractMethod');
        $this->assertHasOwnMethod($obj, 'method');
    }

    public function testShouldNotBuildProtectedAndPrivate()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithNonPublicMethods');
        $this->assertDoesNotHaveOwnMethod($obj, 'privateMethod');
        $this->assertDoesNotHaveOwnMethod($obj, 'protectedMethod');
    }

    public function testShouldBuildAbstractProtected()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithAbstractProtected');
        $this->assertHasOwnMethod($obj, 'protectedMethod');
    }

    public function testShouldBuildInterface()
    {
        $builder = new Builder();
        $obj = $builder->build('TestInterface');
        $class = new ReflectionClass($obj);
        $this->assertTrue(in_array('TestInterface', $class->getInterfaceNames()));
        $this->assertHasOwnMethod($obj, 'method');
    }

    public function testShouldNotBuildFinalMethods()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithFinal');
    }
    
    public function testResultShouldImplementIteratorByDefault()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithParams');
        $this->assertTrue($obj instanceof Iterator);
        $this->assertHasOverriddenIteratorMethods($obj);
    }
    
    public function testResultShouldNotImplementIteratorIfSuperclassImplementsIteratorAggregate()
    {
        // PHP (5.3.2) has a specific fatal error for this: "cannot implement both Iterator and IteratorAggregate at the same time"
        $builder = new Builder();
        $obj = $builder->build('ClassImplementingIteratorAggregate');
        $this->assertFalse($obj instanceof Iterator);
        $this->assertTrue($obj instanceof IteratorAggregate);
        $this->assertHasNotOverriddenIteratorMethods($obj);
    }
    
    public function testResultShouldNotImplementIteratorMethodsIfSuperclassHasAnyIteratorLikeMethods()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassWithIteratorLikeMethods');
        $class = new ReflectionClass($obj);
        $this->assertFalse($class->implementsInterface('Iterator'));
        $this->assertTrue($class->hasMethod('next'));
        $this->assertFalse($class->hasMethod('current'));
        $this->assertTrue(strpos($class->getMethod('next')->getDeclaringClass()->getName(), 'Stub_') === 0);
    }
    
    public function testResultShouldImplementIteratorIfSuperclassImplementsIterator()
    {
        $builder = new Builder();
        $obj = $builder->build('ClassImplementingIterator');
        $class = new ReflectionClass($obj);
        $this->assertTrue($class->implementsInterface('Iterator'));
        $this->assertTrue($class->hasMethod('next'));
        $this->assertHasOverriddenIteratorMethods($obj);
    }

    private function _getBuiltParams($class)
    {
        $builder = new Builder();
        $obj = $builder->build($class);
        $method = new ReflectionMethod($obj, 'method');
        return $method->getParameters();
    }

    public function assertNumberOfParameters($num, $obj, $method)
    {
        $method = new ReflectionMethod($obj, $method);
        return $this->assertEquals($num, $method->getNumberOfParameters());
    }

    public function assertHasOverriddenIteratorMethods($obj)
    {
        foreach ($this->getIteratorMethods() as $method) {
            $this->assertHasOwnMethod($obj, $method);
        }
    }
    
    public function assertHasNotOverriddenIteratorMethods($obj)
    {
        foreach ($this->getIteratorMethods() as $method) {
            if (method_exists($obj, $method)) {
                $this->assertDoesNotHaveOwnMethod($obj, $method);
            }
        }
    }
    
    public function getIteratorMethods()
    {
        return array('current', 'key', 'next', 'rewind', 'valid');
    }

    public function assertHasOwnMethod($obj, $method)
    {
        $method = new ReflectionMethod($obj, $method);
        $message = 'Object does not declare method '
                 . $method->getName() . '.';

        return $this->assertEquals(
            get_class($obj),
            $method->getDeclaringClass()->getName(),
            $message
        );
    }

    public function assertDoesNotHaveOwnMethod($obj, $method)
    {
        $method = new ReflectionMethod($obj, $method);
        $message = 'Object has declared method '
                 . $method->getName() . '.';
        $this->assertTrue($method->getDeclaringClass()->getName() !== get_class($obj), $message);
    }
    
    public function assertParamHasArrayTypehint($obj, $method, $param_index)
    {
        $refl = new ReflectionMethod($obj, $method);
        $params = $refl->getParameters();
        $this->assertTrue($params[$param_index]->isArray());
    }
}

class TestBuilder {
    public function override() {}
}

class ClassWithParams {
    public function method($param1) {}
}

class ClassWithTwoParams {
    public function method($param1, $param2) {}
}

class ClassWithOptionalParam {
    public function method($param1 = 'thedefault') {}
}

class ClassWithClassTypeHint {
    public function method(ClassWithParams $param1) {}
}

class ClassWithArrayTypeHint {
    public function method(array $param1) {}
}

class ClassWithClassTypeHintOnOptionalParam {
    public function method(ClassWithParams $param1 = null) {}
}

class ClassWithArrayTypeHintOnOptionalParam {
    public function method(array $param1 = array(1, 2, 3)) {}
}

class ClassWithMethodsDeclaredInStub {
    public function __construct($param) {}
    public function __destruct() {}
    public function __isset($name) {}
    public function __unset($name) {}
    public function __call($method, $params) {}
    public function __get($name) {}
    public function __set($name, $value) {}
    public static function __callStatic($method, $params) {}
    public function __invoke() {}
    public function __sleep() {}
    public function __wakeup() {}
    public function __toString() {}
    public static function __setState($props = array()) {}
    public function __clone() {}
}

class ClassWithArrayTypehintedMagicMethods {
    public function __call($method, array $params) {}
    public static function __callStatic($method, array $params) {}
    public static function __setState(array $props = array()) {}
}

class ClassWithStaticMethod {
    public static function method($param1) {}
}

abstract class ClassWithAbstractMethod {
    abstract public function method($param1);
}

class ClassWithNonPublicMethods {
    protected function protectedMethod($param1) {}
    private   function privateMethod($param1) {}
}

class ClassWithFinal {
    public final function method() {}
}

abstract class ClassWithAbstractProtected {
    abstract protected function protectedMethod($param1);
}

interface TestInterface {
    public function method();
}

abstract class ClassImplementingIteratorAggregate implements IteratorAggregate {
}

class ClassWithIteratorLikeMethods {
    public function next() {}
}

abstract class ClassImplementingIterator implements Iterator {
}

