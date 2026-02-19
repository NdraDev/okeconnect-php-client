<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

echo "===========================================\n";
echo "  OkeConnect PHP Client - Contoh Lengkap\n";
echo "===========================================\n\n";

echo "CONTOH 1: Inisialisasi Client\n";
echo "-------------------------------------------\n";

try {
    $oke = new OkeConnect('OK00123', '123456', 'secret');
    OkeConnect::setInstance($oke);
    echo "✓ Client berhasil diinisialisasi\n";
    echo "  Member ID: " . $oke->getMemberId() . "\n\n";
} catch (OkeConnectException $e) {
    echo "✗ Error: " . $e->getUserMessage() . "\n\n";
}

echo "CONTOH 2: Transaksi Pulsa (Fixed Denom)\n";
echo "-------------------------------------------\n";

try {
    $response = $oke->transaction('T1', '089660522887', 'TRX' . time());
    
    echo "✓ Response diterima\n";
    echo "  Transaction ID: {$response->transactionId}\n";
    echo "  Ref ID: {$response->refId}\n";
    echo "  Provider: {$response->provider}\n";
    echo "  Nominal: {$response->nominal}\n";
    echo "  Destination: {$response->destination}\n";
    echo "  Status: {$response->getStatusText()}\n";
    
    if ($response->isSuccessful()) {
        echo "  Serial Number: {$response->serialNumber}\n";
    }
    
    if ($response->isFailed()) {
        echo "  Failure Reason: {$response->failureReason}\n";
    }
    
    if ($response->balanceBefore !== null) {
        echo "  Saldo Sebelum: Rp " . number_format($response->balanceBefore, 0, ',', '.') . "\n";
        echo "  Harga: Rp " . number_format($response->price, 0, ',', '.') . "\n";
        echo "  Saldo Setelah: Rp " . number_format($response->balanceAfter, 0, ',', '.') . "\n";
    }
    
} catch (OkeConnectException $e) {
    echo "✗ Transaksi gagal\n";
    echo "  Error: " . $e->getUserMessage() . "\n";
    echo "  Detail: " . $e->getMessage() . "\n";
}

echo "\n";

echo "CONTOH 3: Transaksi E-Wallet (Open Denom)\n";
echo "-------------------------------------------\n";

try {
    $response = $oke->transactionOpenDenom('BBSDN', '085736044280', 50000, 'EW' . time());
    
    echo "✓ Response diterima\n";
    echo "  Transaction ID: {$response->transactionId}\n";
    echo "  Ref ID: {$response->refId}\n";
    echo "  Nominal: {$response->nominal}\n";
    echo "  Status: {$response->getStatusText()}\n";
    
} catch (OkeConnectException $e) {
    echo "✗ Transaksi gagal\n";
    echo "  Error: " . $e->getUserMessage() . "\n";
    
    if ($e->getCode() === OkeConnectException::INVALID_PARAMETER) {
        echo "  Detail: Parameter tidak valid (min 10.000, max 10.000.000)\n";
    }
}

echo "\n";

echo "CONTOH 4: Cek Status Transaksi\n";
echo "-------------------------------------------\n";

try {
    $status = $oke->checkStatus('T5', '08980204060', 'REF123');
    
    echo "✓ Status diterima\n";
    echo "  Ref ID: {$status->refId}\n";
    echo "  Status: {$status->getStatusText()}\n";
    
    if ($status->isSuccessful()) {
        echo "  Serial Number: {$status->serialNumber}\n";
        echo "  Harga: Rp " . number_format($status->price, 0, ',', '.') . "\n";
    }
    
    if ($status->isFailed()) {
        echo "  Failure Reason: {$status->failureReason}\n";
    }
    
    if ($status->isPending()) {
        echo "  Transaksi masih pending, tunggu beberapa saat\n";
    }
    
} catch (OkeConnectException $e) {
    echo "✗ Gagal cek status\n";
    echo "  Error: " . $e->getUserMessage() . "\n";
}

echo "\n";

echo "CONTOH 5: Parse Webhook Callback\n";
echo "-------------------------------------------\n";

$webhookData = [
    'refid' => '114',
    'message' => 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11'
];

try {
    $callback = $oke->parseWebhook($webhookData);
    
    echo "✓ Webhook diparse\n";
    echo "  Transaction ID: {$callback->transactionId}\n";
    echo "  Ref ID: {$callback->refId}\n";
    echo "  Status: {$callback->getStatusText()}\n";
    
    if ($callback->isSuccessful()) {
        echo "  Serial Number: {$callback->serialNumber}\n";
        echo "  Tanggal: {$callback->date}\n";
        echo "  Waktu: {$callback->time}\n";
    }
    
    if ($callback->isFailed()) {
        echo "  Failure Reason: {$callback->failureReason}\n";
    }
    
} catch (OkeConnectException $e) {
    echo "✗ Gagal parse webhook\n";
    echo "  Error: " . $e->getUserMessage() . "\n";
}

echo "\n";

echo "CONTOH 6: Price List\n";
echo "-------------------------------------------\n";

try {
    $products = $oke->getPriceList();
    
    echo "✓ Price list diterima\n";
    echo "  Total Produk: " . count($products) . "\n\n";
    
    echo "  5 Produk Pertama:\n";
    $count = 0;
    foreach ($products as $product) {
        if ($count >= 5) break;
        
        echo "  - {$product->code}: {$product->description}\n";
        echo "    Kategori: {$product->category}\n";
        echo "    Harga: {$product->getFormattedPrice()}\n";
        echo "    Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . "\n\n";
        
        $count++;
    }
    
} catch (OkeConnectException $e) {
    echo "✗ Gagal ambil price list\n";
    echo "  Error: " . $e->getUserMessage() . "\n";
}

echo "\n";

echo "CONTOH 7: Cari Produk Berdasarkan Kode\n";
echo "-------------------------------------------\n";

try {
    $product = $oke->findProductByCode('SMDC150');
    
    if ($product) {
        echo "✓ Produk ditemukan\n";
        echo "  Kode: {$product->code}\n";
        echo "  Deskripsi: {$product->description}\n";
        echo "  Kategori: {$product->category}\n";
        echo "  Harga: {$product->getFormattedPrice()}\n";
        echo "  Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . "\n";
    } else {
        echo "✗ Produk tidak ditemukan\n";
    }
    
} catch (OkeConnectException $e) {
    echo "✗ Error: " . $e->getUserMessage() . "\n";
}

echo "\n";

echo "CONTOH 8: Helper Functions\n";
echo "-------------------------------------------\n";

try {
    $price = oke_price('SMDC150');
    echo "✓ Helper function oke_price()\n";
    echo "  Harga SMDC150: Rp " . number_format($price ?? 0, 0, ',', '.') . "\n\n";
    
    $product = oke_product('SMDC150');
    echo "✓ Helper function oke_product()\n";
    echo "  Produk: " . ($product ? $product->description : 'Tidak ditemukan') . "\n";
    
} catch (OkeConnectException $e) {
    echo "✗ Error: " . $e->getUserMessage() . "\n";
}

echo "\n";
echo "===========================================\n";
echo "  Semua Contoh Selesai!\n";
echo "===========================================\n";
