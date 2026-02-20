<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

$webhookData = [
    'refid' => '114',
    'message' => 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11'
];

try {
    $callback = $oke->parseWebhook($webhookData);

    echo "Transaction ID: " . $callback->transactionId . PHP_EOL;
    echo "Ref ID: " . $callback->refId . PHP_EOL;
    echo "Provider: " . $callback->provider . PHP_EOL;
    echo "Nominal: " . $callback->nominal . PHP_EOL;
    echo "Destination: " . $callback->destination . PHP_EOL;
    echo "Status: " . $callback->getStatusText() . PHP_EOL;

    if ($callback->isSuccessful()) {
        echo "Serial Number: " . $callback->serialNumber . PHP_EOL;
        echo "Date: " . $callback->date . PHP_EOL;
        echo "Time: " . $callback->time . PHP_EOL;
        echo "Full DateTime: " . $callback->getFullDateTime() . PHP_EOL;
    }

    if ($callback->isFailed()) {
        echo "Failure Reason: " . $callback->failureReason . PHP_EOL;
    }

    if ($callback->balanceBefore !== null) {
        echo "Saldo Sebelum: Rp " . number_format($callback->balanceBefore, 0, ',', '.') . PHP_EOL;
        echo "Harga: Rp " . number_format($callback->price, 0, ',', '.') . PHP_EOL;
        echo "Saldo Setelah: Rp " . number_format($callback->balanceAfter, 0, ',', '.') . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
