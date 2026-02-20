<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

$oke = new OkeConnect('OK00123', '123456', 'secret');

try {
    $price = $oke->getPrice('SMDC150');

    if ($price !== null) {
        echo "Kode Produk: SMDC150" . PHP_EOL;
        echo "Harga: Rp " . number_format($price, 0, ',', '.') . PHP_EOL;
    } else {
        echo "Produk tidak ditemukan" . PHP_EOL;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
