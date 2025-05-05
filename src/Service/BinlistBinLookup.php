<?php

namespace App\Service;

use RuntimeException;

class BinlistBinLookup implements BinLookupInterface
{
    private array $binCache = [];

    public function getCountryCode(string $bin): ?string
    {
        if (isset($this->binCache[$bin])) {
            return $this->binCache[$bin];
        }

        usleep(5000000);

        $url = 'https://lookup.binlist.net/' . urlencode($bin);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Commission Calculator',
            CURLOPT_HTTPHEADER => [
                'Accept-Version: 3'
            ]
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 429) {
            throw new RuntimeException("Rate limit exceeded (HTTP 429) while querying BIN $bin.
                                        Please slow down requests or implement persistent caching.");
        }

        if ($httpCode !== 200 || !$response) {
            throw new RuntimeException("Binlist lookup failed for BIN $bin (HTTP $httpCode)");
        }

        $data = json_decode($response, true);
        $countryCode = $data['country']['alpha2'] ?? null;

        $this->binCache[$bin] = $countryCode;
        return $countryCode;
    }
}
