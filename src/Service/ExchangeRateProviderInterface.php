<?php
namespace App\Service;

interface ExchangeRateProviderInterface
{
    public function getRate(string $currency): float;
}
