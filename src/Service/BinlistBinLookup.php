<?php
namespace App\Service;

class BinlistBinLookup implements BinLookupInterface
{
    public function getCountryCode(string $bin): ?string
    {
        $url = 'https://lookup.binlist.net/' . urlencode($bin);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'CommissionCalculator'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return null;

        $data = json_decode($response, true);
        return $data['country']['alpha2'] ?? null;
    }
}
