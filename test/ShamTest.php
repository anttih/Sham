<?php
require_once 'Sham.php';

use sham\Stub;

class ShamTest extends PHPUnit_Framework_TestCase
{
    public function testCreateShouldReturnAStub()
    {
        $this->assertTrue(Sham::create() instanceof Stub);
    }
}

class Test {}
