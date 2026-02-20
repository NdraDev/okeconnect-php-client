<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $availableProducts = $oke->findProductByStatus('1');

    echo "Total Produk Tersedia: " . count($availableProducts) . PHP_EOL . PHP_EOL;

    echo "5 Produk Pertama:" . PHP_EOL;
    $count = 0;
    foreach ($availableProducts as $product) {
        if ($count >= 5) break;

        echo "- " . $product->code . ": " . $product->description . PHP_EOL;
        echo "  Kategori: " . $product->category . PHP_EOL;
        echo "  Harga: " . $product->getFormattedPrice() . PHP_EOL;
        echo PHP_EOL;

        $count++;
    }

    $unavailableProducts = $oke->findProductByStatus('0');
    echo "Total Produk Tidak Tersedia: " . count($unavailableProducts) . PHP_EOL;

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
