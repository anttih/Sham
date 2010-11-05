<?php
require_once 'sham/Sham.php';

use sham\Mock;

class ShamTest extends PHPUnit_Framework_TestCase
{
    public function testCreateShouldReturnAMock()
    {
        $this->assertTrue(Sham::create() instanceof Mock);
    }
}

class Test {}
