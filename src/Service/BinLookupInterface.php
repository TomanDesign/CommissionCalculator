<?php
namespace App\Service;

interface BinLookupInterface
{
    public function getCountryCode(string $bin): ?string;
}
