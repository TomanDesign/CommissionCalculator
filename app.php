<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

use App\CommissionCalculator;
use App\Service\BinlistBinLookup;
use App\Service\ExchangeRateHostProvider;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$calculator = new CommissionCalculator(
    new BinlistBinLookup(),
    new ExchangeRateHostProvider()
);

$filename = $argv[1] ?? null;
if ($filename) {
    $calculator->run($filename);
} else {
    echo "Usage by command line: php app.php input.txt\n";
}
