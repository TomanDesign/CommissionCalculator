<?php

namespace App\Service;

use RuntimeException;

class ExchangeRateHostProvider implements ExchangeRateProviderInterface
{
    public function getRate(string $currency): float
    {
        if ($currency === 'EUR') {
            return 1.0;
        }

        $apiKey = $_ENV['EXCHANGE_API_KEY'] ?? null;
        if (!$apiKey) {
            throw new RuntimeException('Missing EXCHANGE_API_KEY in .env');
        }

        $url = 'https://api.exchangeratesapi.io/latest?access_key=' . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Commission Calculator'
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new RuntimeException("Failed to fetch exchange rates. HTTP code: $httpCode");
        }

        $data = json_decode($response, true);
        return $data['rates'][$currency] ?? 0.0;
    }
}
