<?php
require_once 'Mocker/Builder.php';
class Mocker_BuilderTest extends PHPUnit_Framework_TestCase
{
    public function testMockingAClassShouldReturnInstanceOfSameClass()
    {
        $builder = new Mocker_Builder();
        $this->assertTrue($builder->build('TestBuilder') instanceof TestBuilder);
    }

    public function testOverrideParentMethods()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $this->assertHasOwnMethod($obj, 'override');
    }

    public function testRecordOverridenMethodCalls()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override();
        $this->assertTrue($obj->calls('override')->once());
    }

    public function testRecordCallParams()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override('param 1');
        $this->assertTrue($obj->calls('override', 'param 1')->once());
    }

    public function testBuildMethodWithOneNonOptionalParam()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithParams');
        $this->assertNumberOfParameters(1, $obj, 'method');
    }

    public function testBuildMethodWithTwoNonOptionalParams()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithTwoParams');
        $this->assertNumberOfParameters(2, $obj, 'method');
    }

    public function testBuildMethodWithOneOptionalParam()
    {
        $params = $this->_getBuiltParams('ClassWithOptionalParam');
        $this->assertTrue($params[0]->isOptional());
    }

    public function testShouldUseUniqueDefaultParamValue()
    {
        $params = $this->_getBuiltParams('ClassWithOptionalParam');
        $this->assertEquals(Mocker::NO_VALUE_PASSED, $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithClassTypeHint()
    {
        $params = $this->_getBuiltParams('ClassWithClassTypeHint');
        $this->assertEquals('ClassWithParams', $params[0]->getClass()->getName());
    }

    /**
     * This test will just die unless everything is ok
     */
    public function testDontAdd__call()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithMethodsDeclaredInMock');
    }

    private function _getBuiltParams($class)
    {
        $builder = new Mocker_Builder();
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
    public function method($param1 = null) {}
}

class ClassWithClassTypeHint {
    public function method(ClassWithParams $param1) {}
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

