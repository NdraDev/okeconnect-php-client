<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

header('Content-Type: application/json');

if (empty($_GET['message'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Parameter message tidak boleh kosong',
    ]);
    exit;
}

try {
    $oke = new OkeConnect('OK00123', '123456', 'secret');
    $callback = $oke->parseWebhook($_GET);

    if ($callback->isSuccessful()) {
        echo json_encode([
            'success' => true,
            'ref_id' => $callback->refId,
            'transaction_id' => $callback->transactionId,
            'status' => $callback->status,
            'serial_number' => $callback->serialNumber,
            'destination' => $callback->destination,
            'price' => $callback->price,
        ]);
    } elseif ($callback->isFailed()) {
        echo json_encode([
            'success' => false,
            'ref_id' => $callback->refId,
            'transaction_id' => $callback->transactionId,
            'status' => $callback->status,
            'failure_reason' => $callback->failureReason,
            'destination' => $callback->destination,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'ref_id' => $callback->refId,
            'status' => $callback->status,
        ]);
    }

} catch (OkeConnectException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getUserMessage(),
        'message' => $e->getMessage(),
    ]);
}
