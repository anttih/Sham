<?php
require_once 'Mocker/Builder.php';
class Mocker_BuilderTest extends PHPUnit_Framework_TestCase
{
    public function testMockingAClassShouldReturnInstanceOfSameClass()
    {
        $builder = new Mocker_Builder();
        $this->assertTrue($builder->build('TestBuilder') instanceof TestBuilder);
    }

    public function testShouldOverrideParentMethods()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $this->assertHasOwnMethod($obj, 'override');
    }

    public function testShouldRecordOverridenMethodCalls()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override();
        $this->assertTrue($obj->calls->calls('override')->once());
    }

    public function testShouldRecordCallParams()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('TestBuilder');
        $obj->override('param 1');
        $this->assertTrue($obj->calls->calls('override', 'param 1')->once());
    }

    public function testShouldAddOneNonOptionalParam()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithParams');
        $this->assertNumberOfParameters(1, $obj, 'method');
    }

    public function testShouldAddTwoNonOptionalParams()
    {
        $builder = new Mocker_Builder();
        $obj = $builder->build('ClassWithTwoParams');
        $this->assertNumberOfParameters(2, $obj, 'method');
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
