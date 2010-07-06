<?php
require_once 'Mocker.php';
class MockerTest extends PHPUnit_Framework_TestCase
{
    public function testCreateShouldReturnAMock()
    {
        $this->assertTrue(Mocker::create() instanceof Mocker_Mock);
    }
}

class Test {}
