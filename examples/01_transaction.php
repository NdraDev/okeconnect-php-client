<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $response = $oke->transaction('T1', '089660522887', 'TRX' . time());

    echo "Transaction ID: " . $response->transactionId . PHP_EOL;
    echo "Ref ID: " . $response->refId . PHP_EOL;
    echo "Provider: " . $response->provider . PHP_EOL;
    echo "Nominal: " . $response->nominal . PHP_EOL;
    echo "Destination: " . $response->destination . PHP_EOL;
    echo "Status: " . $response->getStatusText() . PHP_EOL;

    if ($response->isSuccessful()) {
        echo "Serial Number: " . $response->serialNumber . PHP_EOL;
    }

    if ($response->isFailed()) {
        echo "Failure Reason: " . $response->failureReason . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
    echo "Detail: " . $e->getMessage() . PHP_EOL;
}
