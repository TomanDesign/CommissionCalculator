<?php

namespace App;

class CommissionCalculator
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES',
        'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU',
        'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    public function run(string $filename): void
    {
        if (!file_exists($filename)) {
            die("File not found: $filename\n");
        }

        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (!$data || !isset($data['bin'], $data['amount'], $data['currency'])) {
                echo "Invalid line: $line\n";
                continue;
            }

            $bin = $data['bin'];
            $amount = (float)$data['amount'];
            $currency = strtoupper($data['currency']);

            $binData = $this->fetchBinData($bin);
            if (!$binData || !isset($binData['country']['alpha2'])) {
                echo "BIN lookup failed for $bin\n";
                continue;
            }

            $isEu = $this->isEu($binData['country']['alpha2']);
            $rate = $this->getExchangeRate($currency);
            if ($rate === 0.0) {
                echo "Exchange rate not available for $currency\n";
                continue;
            }

            $amountInEur = $this->convertToEur($amount, $currency, $rate);
            $commission = $this->calculateCommission($amountInEur, $isEu);

            echo number_format($commission, 10, '.', '') . PHP_EOL;
        }
    }

    private function fetchBinData(string $bin): ?array
    {
        $url = 'https://lookup.binlist.net/' . urlencode($bin);
        $response = $this->curlGet($url);
        return $response ? json_decode($response, true) : null;
    }

    private function getExchangeRate(string $currency): float
    {
        if ($currency === 'EUR') {
            return 1.0;
        }

        $url = 'https://api.exchangerate.host/latest';
        $response = $this->curlGet($url);
        $data = $response ? json_decode($response, true) : null;

        return $data['rates'][$currency] ?? 0.0;
    }

    private function curlGet(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PHP Commission Calculator',
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch) . PHP_EOL;
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo "HTTP $httpCode returned for URL: $url\n";
            return null;
        }

        return $response;
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
