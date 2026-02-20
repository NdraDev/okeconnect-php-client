<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $products = $oke->getPriceList();

    echo "Total Produk: " . count($products) . PHP_EOL . PHP_EOL;

    echo "5 Produk Pertama:" . PHP_EOL;
    $count = 0;
    foreach ($products as $product) {
        if ($count >= 5) break;

        echo "- " . $product->code . ": " . $product->description . PHP_EOL;
        echo "  Kategori: " . $product->category . PHP_EOL;
        echo "  Produk: " . $product->product . PHP_EOL;
        echo "  Harga: " . $product->getFormattedPrice() . PHP_EOL;
        echo "  Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . PHP_EOL;
        echo PHP_EOL;

        $count++;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
