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
        $this->assertTrue($obj->calls->calls('override')->once());
    }

    public function testRecordCallParams()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override('param 1');
        $this->assertTrue($obj->calls->calls('override', 'param 1')->once());
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

    public function testBuildMethodWithNullParam()
    {
        $params = $this->_getBuiltParams('ClassWithOptionalParam');
        $this->assertEquals(null, $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithStringParam()
    {
        $params = $this->_getBuiltParams('ClassWithStringParam');
        $this->assertEquals('default', $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithIntegerParam()
    {
        $params = $this->_getBuiltParams('ClassWithIntegerParam');
        $this->assertTrue($params[0]->getDefaultValue() === 0);
    }

    public function testBuildMethodWithArrayParam()
    {
        $params = $this->_getBuiltParams('ClassWithArrayParam');
        $this->assertEquals(array(), $params[0]->getDefaultValue());
    }
    
    public function testBuildMethodWithClassTypeHint()
    {
        $params = $this->_getBuiltParams('ClassWithClassTypeHint');
        $this->assertEquals('ClassWithParams', $params[0]->getClass()->getName());
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

class ClassWithIntegerParam {
    public function method($param1 = 0) {}
}

class ClassWithStringParam {
    public function method($param1 = 'default') {}
}

class ClassWithArrayParam {
    public function method($param1 = array()) {}
}

class ClassWithClassTypeHint {
    public function method(ClassWithParams $param1) {}
}
