<?php
require_once 'Sham.php';
class ShamTest extends PHPUnit_Framework_TestCase
{
    public function testCreateShouldReturnAMock()
    {
        $this->assertTrue(Sham::create() instanceof Sham_Mock);
    }
}

class Test {}
