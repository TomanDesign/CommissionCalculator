<?php

require_once __DIR__ . '/src/CommissionCalculator.php';

use App\CommissionCalculator;

$filename = $argv[1] ?? null;
if (!$filename) {
    echo "Usage: php app.php input.txt\n";
    exit(1);
}

$calculator = new CommissionCalculator();
$calculator->run($filename);
