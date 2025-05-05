<?php
namespace App\Service;

class ExchangeRateHostProvider implements ExchangeRateProviderInterface
{
    public function getRate(string $currency): float
    {
        if ($currency === 'EUR') return 1.0;

        $ch = curl_init('https://api.exchangerate.host/latest');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['rates'][$currency] ?? 0.0;
    }
}
