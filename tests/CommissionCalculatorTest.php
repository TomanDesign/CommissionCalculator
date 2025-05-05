<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\CommissionCalculator;
use App\Service\BinLookupInterface;
use App\Service\ExchangeRateProviderInterface;

class CommissionCalculatorTest extends TestCase
{
    private string $tempFile;

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function createTempInput(string $jsonLine): string
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'commission_input_');
        file_put_contents($this->tempFile, $jsonLine);
        return $this->tempFile;
    }

    public function testCommissionForEuTransaction(): void
    {
        // Arrange
        $binMock = $this->createMock(BinLookupInterface::class);
        $binMock->method('getCountryCode')->willReturn('DE'); // EU country

        $rateMock = $this->createMock(ExchangeRateProviderInterface::class);
        $rateMock->method('getRate')->willReturn(1.0); // EUR rate

        $calculator = new CommissionCalculator($binMock, $rateMock);

        $file = $this->createTempInput('{"bin":"45717360","amount":"100.00","currency":"EUR"}');

        // Act
        ob_start();
        $calculator->run($file);
        $output = trim(ob_get_clean());

        // Assert
        $this->assertEquals("1.0000000000", $output);
    }

    public function testCommissionForNonEuTransaction(): void
    {
        // Arrange
        $binMock = $this->createMock(BinLookupInterface::class);
        $binMock->method('getCountryCode')->willReturn('US'); // Non-EU

        $rateMock = $this->createMock(ExchangeRateProviderInterface::class);
        $rateMock->method('getRate')->willReturn(2.0); // Let's say $1 = 0.5 EUR

        $calculator = new CommissionCalculator($binMock, $rateMock);

        $file = $this->createTempInput('{"bin":"516793","amount":"100.00","currency":"USD"}');

        // Act
        ob_start();
        $calculator->run($file);
        $output = trim(ob_get_clean());

        // 100 USD / 2.0 = 50 EUR → 50 * 0.02 = 1.0000000000
        $this->assertEquals("1.0000000000", $output);
    }

    public function testSkipsInvalidJson(): void
    {
        // Arrange
        $binMock = $this->createMock(BinLookupInterface::class);
        $rateMock = $this->createMock(ExchangeRateProviderInterface::class);
        $calculator = new CommissionCalculator($binMock, $rateMock);

        $file = $this->createTempInput('INVALID LINE');

        // Act
        ob_start();
        $calculator->run($file);
        $output = trim(ob_get_clean());

        // Assert
        $this->assertMatchesRegularExpression('/Invalid line:/', $output);
    }
}
