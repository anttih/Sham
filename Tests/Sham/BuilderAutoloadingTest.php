<?php
class Sham_BuilderAutoloadingTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function teardown()
    {
        spl_autoload_unregister(array($this, 'autoload'));
    }

    public function test()
    {
        $builder = new Sham_Builder();
        $obj = $builder->build('Sham_Fixture');
        $this->assertTrue($obj instanceof Sham_Fixture);
    }

    public function autoload($class)
    {
        require_once __dir__ . '/Fixture.php';
    }
}
