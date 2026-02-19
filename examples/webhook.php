<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;

header('Content-Type: application/json');

$oke = new OkeConnect('OK00123', '123456', 'secret');

if (empty($_GET['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message parameter']);
    exit;
}

$callback = $oke->parseWebhook($_GET);

if ($callback->isSuccessful()) {
    echo json_encode([
        'success' => true,
        'ref_id' => $callback->refId,
        'transaction_id' => $callback->transactionId,
        'status' => $callback->status,
        'serial_number' => $callback->serialNumber,
    ]);
} elseif ($callback->isFailed()) {
    echo json_encode([
        'success' => false,
        'ref_id' => $callback->refId,
        'transaction_id' => $callback->transactionId,
        'status' => $callback->status,
        'failure_reason' => $callback->failureReason,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'ref_id' => $callback->refId,
        'status' => $callback->status,
    ]);
}
