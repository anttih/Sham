<?php
require_once 'Sham/Builder.php';
class Sham_BuilderTest extends PHPUnit_Framework_TestCase
{
    public function testMockingAClassShouldReturnInstanceOfSameClass()
    {
        $builder = new Sham_Builder();
        $this->assertTrue($builder->build('TestBuilder') instanceof TestBuilder);
    }

    public function testOverrideParentMethods()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('TestBuilder');
        $this->assertHasOwnMethod($obj, 'override');
    }

    public function testRecordOverridenMethodCalls()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override();
        $this->assertTrue($obj->calls('override')->once());
    }

    public function testRecordCallParams()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override('param 1');
        $this->assertTrue($obj->calls('override', 'param 1')->once());
    }
    
    public function testRecordOptionalParamsWhenGiven()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithClassTypeHintOnOptionalParam');
        $param = new ClassWithParams();
        $obj->method(null);
        $obj->method($param);
        $this->assertTrue($obj->calls('method', null)->once());
        $this->assertTrue($obj->calls('method', $param)->once());
    }
    
    public function testDontRecordOptionalParamsWhenNotGiven()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithClassTypeHintOnOptionalParam');
        $obj->method();
        $this->assertEquals(array(), $obj->calls('method')->first()->params);
    }

    public function testBuildMethodWithOneNonOptionalParam()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithParams');
        $this->assertNumberOfParameters(1, $obj, 'method');
    }

    public function testBuildMethodWithTwoNonOptionalParams()
    {
        $builder = new Sham_Builder();
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
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithMethodsDeclaredInMock');
    }
    
    public function testShouldPreserveArrayTypehintInInheritedMagicMethods()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithArrayTypehintedMagicMethods');
        
        $this->assertParamHasArrayTypehint($obj, '__call', 1);
        $this->assertParamHasArrayTypehint($obj, '__callStatic', 1);
        $this->assertParamHasArrayTypehint($obj, '__setState', 0);
    }

    public function testBuildAbstractClass()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithAbstractMethod');
        $this->assertHasOwnMethod($obj, 'method');
    }

    public function testShouldNotBuildProtectedAndPrivate()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithNonPublicMethods');
        $this->assertDoesNotHaveOwnMethod($obj, 'privateMethod');
        $this->assertDoesNotHaveOwnMethod($obj, 'protectedMethod');
    }

    public function testShouldBuildAbstractProtected()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('ClassWithAbstractProtected');
        $this->assertHasOwnMethod($obj, 'protectedMethod');
    }

    public function testShouldBuildInterface()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('TestInterface');
        $class = new ReflectionClass($obj);
        $this->assertTrue(in_array('TestInterface', $class->getInterfaceNames()));
        $this->assertHasOwnMethod($obj, 'method');
    }

    private function _getBuiltParams($class)
    {
        $builder = new Sham_Builder();
        $obj = $builder->build($class);
        $method = new ReflectionMethod($obj, 'method');
        return $method->getParameters();
    }

    public function assertNumberOfParameters($num, $obj, $method)
    {
        $method = new ReflectionMethod($obj, $method);
        return $this->assertEquals($num, $method->getNumberOfParameters());
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
        $this->assertTrue($method->getDeclaringClass()->getName() !== get_class($obj));
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

class ClassWithMethodsDeclaredInMock {
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

abstract class ClassWithAbstractMethod {
    abstract public function method($param1);
}

class ClassWithNonPublicMethods {
    protected function protectedMethod($param1) {}
    private   function privateMethod($param1) {}
}

abstract class ClassWithAbstractProtected {
    abstract protected function protectedMethod($param1);
}

interface TestInterface {
    public function method();
}

