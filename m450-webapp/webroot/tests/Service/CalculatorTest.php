<?php

namespace Test\Service;
use App\Service\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase {
    public function testCToF() {
        $calc = new Calculator();
        $result = $calc->cToF(0);
        $this->assertIsFloat($result);
        $this->assertEquals(32.0, $result);    
    }
}