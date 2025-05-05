<?php

use PHPUnit\Framework\TestCase;
use App\CommissionCalculator;

require_once __DIR__ . '/../src/CommissionCalculator.php';

class CommissionCalculatorTest extends TestCase
{
    private CommissionCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CommissionCalculator();
    }

    public function testIsEu(): void
    {
        $this->assertTrue($this->calculator->isEu('DE'));
        $this->assertFalse($this->calculator->isEu('US'));
    }

    public function testConvertToEur(): void
    {
        $this->assertEquals(100.0, $this->calculator->convertToEur(100, 'EUR', 0));
        $this->assertEquals(50.0, $this->calculator->convertToEur(100, 'USD', 2.0));
    }

    public function testCalculateCommission(): void
    {
        $this->assertEquals(1.0, $this->calculator->calculateCommission(100, true));
        $this->assertEquals(2.0, $this->calculator->calculateCommission(100, false));
    }

    public function testParseLine(): void
    {
        $line = '"bin":"45717360","amount":"100.00","currency":"EUR"';
        $parsed = $this->calculator->parseLine($line);
        $this->assertEquals('45717360', $parsed['bin']);
        $this->assertEquals(100.0, $parsed['amount']);
        $this->assertEquals('EUR', $parsed['currency']);
    }

    public function testParseLineInvalid(): void
    {
        $line = 'INVALID_JSON';
        $this->assertNull($this->calculator->parseLine($line));
    }
}
