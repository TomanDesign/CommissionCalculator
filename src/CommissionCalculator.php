<?php

namespace App;

use App\Service\BinLookupInterface;
use App\Service\ExchangeRateProviderInterface;

class CommissionCalculator
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES',
        'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU',
        'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    private BinLookupInterface $binLookup;
    private ExchangeRateProviderInterface $exchangeRateProvider;

    public function __construct(
        BinLookupInterface $binLookup,
        ExchangeRateProviderInterface $exchangeRateProvider
    ) {
        $this->binLookup = $binLookup;
        $this->exchangeRateProvider = $exchangeRateProvider;
    }

    public function run(string $filename): void
    {
        if (!file_exists($filename)) {
            echo "File not found: $filename\n";
            return;
        }

        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (!$data || !isset($data['bin'], $data['amount'], $data['currency'])) {
                echo "Invalid line: $line\n";
                continue;
            }

            $bin = $data['bin'];
            $amount = (float) $data['amount'];
            $currency = strtoupper($data['currency']);

            $countryCode = $this->binLookup->getCountryCode($bin);
            if (!$countryCode) {
                echo "BIN lookup failed for $bin\n";
                continue;
            }

            $isEu = $this->isEu($countryCode);
            $rate = $this->exchangeRateProvider->getRate($currency);
            if ($rate === 0.0) {
                echo "Exchange rate unavailable for $currency\n";
                continue;
            }

            $amountInEur = $this->convertToEur($amount, $currency, $rate);
            $commission = $this->calculateCommission($amountInEur, $isEu);

            echo number_format($commission, 10, '.', '') . PHP_EOL;
        }
    }

    private function convertToEur(float $amount, string $currency, float $rate): float
    {
        return ($currency === 'EUR' || $rate === 0.0) ? $amount : $amount / $rate;
    }

    private function calculateCommission(float $amountInEur, bool $isEu): float
    {
        return $amountInEur * ($isEu ? 0.01 : 0.02);
    }

    private function isEu(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), self::EU_COUNTRIES, true);
    }
}
