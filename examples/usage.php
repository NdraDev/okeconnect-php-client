<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\Parsers\TransactionParser;
use OkeConnect\Parsers\StatusCheckParser;
use OkeConnect\Parsers\WebhookParser;
use OkeConnect\Parsers\PriceListParser;

echo "===========================================\n";
echo "  OkeConnect PHP Client - Test Parsing\n";
echo "===========================================\n\n";

echo "1. TEST TRANSACTION PARSER\n";
echo "-------------------------------------------\n";

$transactionParser = new TransactionParser();

$processing = 'T#210286229 R#113 Three 1.000 T1.089660522887 akan diproses. Saldo 279.655 - 1.321 = 278.334 @19:08';
$result = $transactionParser->parse($processing);
echo "Processing:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Provider: {$result->provider}\n";
echo "  Nominal: {$result->nominal}\n";
echo "  Product: {$result->productCode}\n";
echo "  Destination: {$result->destination}\n";
echo "  Balance Before: {$result->balanceBefore}\n";
echo "  Price: {$result->price}\n";
echo "  Balance After: {$result->balanceAfter}\n";
echo "  Time: {$result->time}\n";
echo "  Is Processing: " . ($result->isProcessing() ? 'Yes' : 'No') . "\n\n";

$success = 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11';
$result = $transactionParser->parse($success);
echo "Success:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Serial Number: {$result->serialNumber}\n";
echo "  Date: 12/05 Time: {$result->time}\n";
echo "  Is Successful: " . ($result->isSuccessful() ? 'Yes' : 'No') . "\n\n";

$failed = 'T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo 10.795.667 @22:15';
$result = $transactionParser->parse($failed);
echo "Failed:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Failure Reason: {$result->failureReason}\n";
echo "  Is Failed: " . ($result->isFailed() ? 'Yes' : 'No') . "\n\n";

$openDenom = 'T#762261897 R#7777 H2H DANA Topup (Bebas Nominal) BBSDN.085736044280 , QTY : 12345 akan diproses. Saldo 43.928.256 - 12.516 = 43.915.740 @19:14';
$result = $transactionParser->parse($openDenom);
echo "Open Denom:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Nominal (QTY): {$result->nominal}\n";
echo "  Is Processing: " . ($result->isProcessing() ? 'Yes' : 'No') . "\n\n";

echo "2. TEST STATUS CHECK PARSER\n";
echo "-------------------------------------------\n";

$statusParser = new StatusCheckParser();

$statusSuccess = 'R#999 Three 5.000 T5.08980204060 sudah pernah jam 18:46, status Sukses. SN: R25042218462100b7. Hrg 6.487';
$result = $statusParser->parse($statusSuccess);
echo "Status Success:\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Provider: {$result->provider}\n";
echo "  Nominal: {$result->nominal}\n";
echo "  Time: {$result->transactionTime}\n";
echo "  SN: {$result->serialNumber}\n";
echo "  Price: {$result->price}\n";
echo "  Is Successful: " . ($result->isSuccessful() ? 'Yes' : 'No') . "\n\n";

$statusFailed = 'R#999 Three 5.000 T5.08980204060 sudah pernah jam 18:46, status Gagal. Mohon diperiksa kembali No tujuan.';
$result = $statusParser->parse($statusFailed);
echo "Status Failed:\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Failure Reason: {$result->failureReason}\n";
echo "  Is Failed: " . ($result->isFailed() ? 'Yes' : 'No') . "\n\n";

$statusPending = 'Mhn tunggu trx sblmnya selesai: T#762221212 R#999 T5.08980204060 @18:46, status Menunggu Jawaban.';
$result = $statusParser->parse($statusPending);
echo "Status Pending:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Time: {$result->transactionTime}\n";
echo "  Is Pending: " . ($result->isPending() ? 'Yes' : 'No') . "\n\n";

$statusNoData = 'TIDAK ADA transaksi Tujuan 08980204060 pada tgl 22/04/2025. Tidak ada data.';
$result = $statusParser->parse($statusNoData);
echo "Status No Data:\n";
echo "  Destination: {$result->destination}\n";
echo "  Date: {$result->transactionTime}\n";
echo "  Is No Data: " . ($result->isNoData() ? 'Yes' : 'No') . "\n\n";

echo "3. TEST WEBHOOK PARSER\n";
echo "-------------------------------------------\n";

$webhookParser = new WebhookParser();

$webhookSuccess = 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11';
$result = $webhookParser->parse($webhookSuccess);
echo "Webhook Success:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Serial Number: {$result->serialNumber}\n";
echo "  Date: {$result->date}\n";
echo "  Time: {$result->time}\n";
echo "  Balance Before: {$result->balanceBefore}\n";
echo "  Price: {$result->price}\n";
echo "  Balance After: {$result->balanceAfter}\n";
echo "  Is Successful: " . ($result->isSuccessful() ? 'Yes' : 'No') . "\n\n";

$webhookFailed = 'T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo 10.795.667 @22:15';
$result = $webhookParser->parse($webhookFailed);
echo "Webhook Failed:\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Ref ID: {$result->refId}\n";
echo "  Failure Reason: {$result->failureReason}\n";
echo "  Is Failed: " . ($result->isFailed() ? 'Yes' : 'No') . "\n\n";

$webhookQuery = [
    'refid' => '114',
    'message' => 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11'
];
$result = $webhookParser->parseFromQuery($webhookQuery);
echo "Webhook From Query:\n";
echo "  Ref ID (from query): {$result->refId}\n";
echo "  Transaction ID: {$result->transactionId}\n";
echo "  Is Successful: " . ($result->isSuccessful() ? 'Yes' : 'No') . "\n\n";

echo "4. TEST PRICE LIST PARSER\n";
echo "-------------------------------------------\n";

$priceListParser = new PriceListParser();

$json = '[
    {"kode":"SMDC150","keterangan":"Smart 30GB All + 60GB (01-05) 30 Hari","produk":"Data Smart Combo","kategori":"KUOTA SMARTFREN","harga":"134600","status":"1"},
    {"kode":"SMDC10","keterangan":"Smart 2GB All + 2GB (01-05) + 2GB Chat 7 Hari","produk":"Data Smart Combo","kategori":"KUOTA SMARTFREN","harga":"15050","status":"1"},
    {"kode":"T1","keterangan":"Telkomsel 1000","produk":"Pulsa","kategori":"TELKOMSEL","harga":"1321","status":"1"}
]';

$items = $priceListParser->parse($json);
echo "Parse Price List:\n";
echo "  Total Items: " . count($items) . "\n";
foreach ($items as $item) {
    echo "  - {$item->code}: {$item->description} ({$item->getFormattedPrice()})\n";
}
echo "\n";

$product = $priceListParser->findByCode($json, 'SMDC150');
echo "Find By Code (SMDC150):\n";
echo "  Description: {$product->description}\n";
echo "  Price: {$product->getFormattedPrice()}\n";
echo "  Available: " . ($product->isAvailable() ? 'Yes' : 'No') . "\n\n";

$byCategory = $priceListParser->parseByCategory($json, 'SMARTFREN');
echo "Parse By Category (SMARTFREN):\n";
echo "  Items: " . count($byCategory) . "\n\n";

$available = $priceListParser->getAvailable($items);
echo "Get Available:\n";
echo "  Items: " . count($available) . "\n\n";

$grouped = $priceListParser->groupByCategory($items);
echo "Group By Category:\n";
foreach ($grouped as $category => $catItems) {
    echo "  {$category}: " . count($catItems) . " items\n";
}
echo "\n";

$array = $priceListParser->toArray($items);
echo "To Array:\n";
print_r($array[0]);
echo "\n";

echo "===========================================\n";
echo "  All Tests Completed!\n";
echo "===========================================\n";
