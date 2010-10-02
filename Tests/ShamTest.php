<?php
require_once 'Sham.php';

use Sham\Mock;

class ShamTest extends PHPUnit_Framework_TestCase
{
    public function testCreateShouldReturnAMock()
    {
        $this->assertTrue(Sham::create() instanceof Mock);
    }
}

class Test {}
