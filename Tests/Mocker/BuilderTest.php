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
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithOptionalParam');
        $method = new ReflectionMethod($obj, 'method');
        $params = $method->getParameters();
        $this->assertTrue($params[0]->isOptional());
    }

    public function testBuildMethodWithNullParam()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithOptionalParam');
        $method = new ReflectionMethod($obj, 'method');
        $params = $method->getParameters();
        $this->assertEquals(null, $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithStringParam()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithStringParam');
        $method = new ReflectionMethod($obj, 'method');
        $params = $method->getParameters();
        $this->assertEquals('default', $params[0]->getDefaultValue());
    }

    public function testBuildMethodWithIntegerParam()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithIntegerParam');
        $method = new ReflectionMethod($obj, 'method');
        $params = $method->getParameters();
        $this->assertTrue($params[0]->getDefaultValue() === 0);
    }

    public function testBuildMethodWithClassTypeHint()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithClassTypeHint');
        $method = new ReflectionMethod($obj, 'method');
        $params = $method->getParameters();
        $this->assertEquals('ClassWithParams', $params[0]->getClass()->getName());
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

class ClassWithClassTypeHint {
    public function method(ClassWithParams $param1) {}
}
