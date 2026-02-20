<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $status = $oke->checkStatus('T5', '08980204060', 'REF123');

    echo "Ref ID: " . $status->refId . PHP_EOL;
    echo "Provider: " . $status->provider . PHP_EOL;
    echo "Nominal: " . $status->nominal . PHP_EOL;
    echo "Destination: " . $status->destination . PHP_EOL;
    echo "Status: " . $status->getStatusText() . PHP_EOL;

    if ($status->isSuccessful()) {
        echo "Serial Number: " . $status->serialNumber . PHP_EOL;
        echo "Transaction Time: " . $status->transactionTime . PHP_EOL;
        echo "Price: Rp " . number_format($status->price, 0, ',', '.') . PHP_EOL;
    }

    if ($status->isFailed()) {
        echo "Failure Reason: " . $status->failureReason . PHP_EOL;
    }

    if ($status->isPending()) {
        echo "Transaksi masih pending, tunggu beberapa saat" . PHP_EOL;
    }

    if ($status->isNoData()) {
        echo "Data tidak ditemukan" . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
