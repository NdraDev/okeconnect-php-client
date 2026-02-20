<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $refId = 'CEKSALDO' . time();
    $response = $oke->transaction('T1', '089660522887', $refId);

    if ($response->balanceBefore !== null) {
        echo "Saldo Sebelum: Rp " . number_format($response->balanceBefore, 0, ',', '.') . PHP_EOL;
        echo "Harga: Rp " . number_format($response->price, 0, ',', '.') . PHP_EOL;
        echo "Saldo Setelah: Rp " . number_format($response->balanceAfter, 0, ',', '.') . PHP_EOL;
    } else {
        echo "Informasi saldo tidak tersedia" . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
