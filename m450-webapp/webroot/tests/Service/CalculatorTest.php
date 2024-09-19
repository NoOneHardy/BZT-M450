<?php

namespace Test\Service;
use App\Service\Calculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{

    public static function cToFData(): array
    {
        return [
            ['celsius' => 0.0, 'fahrenheit' => 32.0],
            ['celsius' => 32.0, 'fahrenheit' => 89.6],
            ['celsius' => -100.0, 'fahrenheit' => -148.0],
            ['celsius' => 10000.0, 'fahrenheit' => 18032.0],
        ];
    }

    #[DataProvider('cToFData')]
    public function testCToF(float $celsius, float $fahrenheit)
    {
        $calc = new Calculator();
        $result = $calc->cToF($celsius);
        $this->assertIsFloat($result);
        $this->assertEquals($fahrenheit, $result);
    }

    public function testCToFArray()
    {
        $calc = new Calculator();
        $result = $calc->cToF([0, 32, 100]);
        $this->assertIsArray($result);
        $this->assertEquals([32.0, 89.6, 212.0], $result);
    }

    public function testCToFArrayStrings()
    {
        $calc = new Calculator();
        $result = $calc->cToF(['test' => 'error', '32', '100']);
        $this->assertIsArray($result);
        $this->assertEquals([32.0, 89.6, 212.0], $result);
    }

    public function testCToFNull()
    {
        $calc = new Calculator();
        try {
            $calc->cToF(null);
        } catch (\TypeError $e) {
            $this->assertTrue(true);
            return;
        }
    }
}