<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $product = $oke->findProductByCode('SMDC150');

    if ($product) {
        echo "Kode: " . $product->code . PHP_EOL;
        echo "Deskripsi: " . $product->description . PHP_EOL;
        echo "Produk: " . $product->product . PHP_EOL;
        echo "Kategori: " . $product->category . PHP_EOL;
        echo "Harga: " . $product->getFormattedPrice() . PHP_EOL;
        echo "Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . PHP_EOL;
    } else {
        echo "Produk tidak ditemukan" . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
