<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $products = $oke->findProductByCategory('SMARTFREN');

    echo "Total Produk Ditemukan: " . count($products) . PHP_EOL . PHP_EOL;

    foreach ($products as $product) {
        echo "- " . $product->code . ": " . $product->description . PHP_EOL;
        echo "  Kategori: " . $product->category . PHP_EOL;
        echo "  Harga: " . $product->getFormattedPrice() . PHP_EOL;
        echo "  Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . PHP_EOL;
        echo PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
