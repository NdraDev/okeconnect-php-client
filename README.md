# OkeConnect PHP Client

Package PHP untuk integrasi API OkeConnect H2H dengan parsing lengkap untuk semua response.

**Requirement:** PHP 8.2 atau lebih baru

**Developer:** NdraDeveloper

---

## Instalasi

```bash
composer require ndradev/okeconnect-php-client
```

---

## Cara Penggunaan

### 1. Inisialisasi

```php
use OkeConnect\OkeConnect;

$oke = new OkeConnect(
    memberId: 'OK00123',
    pin: '123456',
    password: 'secret'
);
```

---

### 2. Transaksi Pulsa (Fixed Denom)

```php
$response = $oke->transaction('T1', '089660522887', 'TRX123');

echo $response->transactionId;
echo $response->refId;
echo $response->provider;
echo $response->nominal;
echo $response->productCode;
echo $response->destination;
echo $response->balanceBefore;
echo $response->price;
echo $response->balanceAfter;
echo $response->time;
echo $response->serialNumber;

if ($response->isProcessing()) {
    echo "Sedang diproses";
}

if ($response->isSuccessful()) {
    echo "Berhasil";
}

if ($response->isFailed()) {
    echo "Gagal: " . $response->failureReason;
}

print_r($response->toArray());
```

---

### 3. Transaksi E-Wallet (Open Denom)

```php
$response = $oke->transactionOpenDenom('BBSDN', '085736044280', 50000, 'EW123');

echo $response->nominal;
echo $response->transactionId;
```

---

### 4. Cek Status Transaksi

```php
$status = $oke->checkStatus('T5', '08980204060', 'REF123');

echo $status->refId;
echo $status->provider;
echo $status->nominal;
echo $status->productCode;
echo $status->destination;
echo $status->transactionTime;
echo $status->serialNumber;
echo $status->price;

if ($status->isSuccessful()) {
    echo "Transaksi sukses";
}

if ($status->isFailed()) {
    echo "Transaksi gagal: " . $status->failureReason;
}

if ($status->isPending()) {
    echo "Transaksi pending";
}

if ($status->isNoData()) {
    echo "Data tidak ditemukan";
}

print_r($status->toArray());
```

---

### 5. Parse Webhook Callback

```php
$callback = $oke->parseWebhook($_GET);

echo $callback->transactionId;
echo $callback->refId;
echo $callback->provider;
echo $callback->nominal;
echo $callback->productCode;
echo $callback->destination;
echo $callback->serialNumber;
echo $callback->balanceBefore;
echo $callback->price;
echo $callback->balanceAfter;
echo $callback->date;
echo $callback->time;

if ($callback->isSuccessful()) {
    echo "Webhook sukses";
}

if ($callback->isFailed()) {
    echo "Webhook gagal: " . $callback->failureReason;
}

print_r($callback->toArray());
```

---

### 6. Price List

```php
$products = $oke->getPriceList();

foreach ($products as $product) {
    echo $product->code;
    echo $product->description;
    echo $product->product;
    echo $product->category;
    echo $product->getFormattedPrice();
    
    if ($product->isAvailable()) {
        echo "Tersedia";
    }
}

$produk = $oke->findProductByCode('SMDC150');
if ($produk) {
    echo $produk->description;
    echo "Rp " . number_format($produk->price, 0, ',', '.');
}

$harga = $oke->getPrice('SMDC150');

$available = $oke->getAvailableProducts();

$byCategory = $oke->getPriceListByCategory('SMARTFREN');

$byProduct = $oke->getPriceListByProduct('Data Smart Combo');
```

---

### 7. Helper Functions

```php
$response = oke_transaction('T1', '089660522887', 'REF123');
$response = oke_topup('BBSDN', '085736044280', 50000, 'REF123');
$status = oke_check_status('T5', '08980204060', 'REF123');
$callback = oke_webhook($_GET);
$products = oke_products();
$product = oke_product('SMDC150');
$price = oke_price('SMDC150');
```

---

## Contoh Webhook Handler

```php
<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;

header('Content-Type: application/json');

$oke = new OkeConnect('OK00123', '123456', 'secret');
$callback = $oke->parseWebhook($_GET);

if ($callback->isSuccessful()) {
    echo json_encode([
        'success' => true,
        'ref_id' => $callback->refId,
        'transaction_id' => $callback->transactionId,
        'serial_number' => $callback->serialNumber,
    ]);
}

if ($callback->isFailed()) {
    echo json_encode([
        'success' => false,
        'ref_id' => $callback->refId,
        'failure_reason' => $callback->failureReason,
    ]);
}
```

---

## Response Models

### TransactionResponse

| Property | Tipe | Keterangan |
|----------|------|------------|
| transactionId | string|null | ID Transaksi |
| refId | string|null | ID Referensi |
| provider | string|null | Provider (Three, Telkomsel, dll) |
| nominal | string|null | Nominal transaksi |
| productCode | string|null | Kode produk |
| destination | string|null | Nomor tujuan |
| status | string|null | Status (SUCCESS/FAILED/PROCESSING) |
| balanceBefore | float|null | Saldo sebelum |
| price | float|null | Harga transaksi |
| balanceAfter | float|null | Saldo setelah |
| time | string|null | Waktu transaksi |
| serialNumber | string|null | Serial number (jika sukses) |
| failureReason | string|null | Alasan gagal (jika gagal) |

**Methods:**
- `isProcessing()` - Cek apakah sedang diproses
- `isSuccessful()` - Cek apakah berhasil
- `isFailed()` - Cek apakah gagal
- `getStatusText()` - Dapatkan teks status
- `toArray()` - Convert ke array
- `toJson()` - Convert ke JSON

---

### StatusCheckResponse

| Property | Tipe | Keterangan |
|----------|------|------------|
| refId | string|null | ID Referensi |
| provider | string|null | Provider |
| nominal | string|null | Nominal |
| productCode | string|null | Kode produk |
| destination | string|null | Nomor tujuan |
| transactionTime | string|null | Waktu transaksi |
| status | string|null | Status |
| serialNumber | string|null | Serial number |
| price | float|null | Harga |
| failureReason | string|null | Alasan gagal |
| transactionId | string|null | ID Transaksi |

**Methods:**
- `isSuccessful()` - Cek apakah sukses
- `isFailed()` - Cek apakah gagal
- `isPending()` - Cek apakah pending
- `isNoData()` - Cek apakah tidak ada data
- `getStatusText()` - Dapatkan teks status
- `toArray()` - Convert ke array
- `toJson()` - Convert ke JSON

---

### WebhookCallback

| Property | Tipe | Keterangan |
|----------|------|------------|
| transactionId | string|null | ID Transaksi |
| refId | string|null | ID Referensi |
| provider | string|null | Provider |
| nominal | string|null | Nominal |
| productCode | string|null | Kode produk |
| destination | string|null | Nomor tujuan |
| status | string|null | Status |
| serialNumber | string|null | Serial number |
| balanceBefore | float|null | Saldo sebelum |
| price | float|null | Harga |
| balanceAfter | float|null | Saldo setelah |
| date | string|null | Tanggal |
| time | string|null | Waktu |
| failureReason | string|null | Alasan gagal |

**Methods:**
- `isSuccessful()` - Cek apakah sukses
- `isFailed()` - Cek apakah gagal
- `getStatusText()` - Dapatkan teks status
- `getFullDateTime()` - Dapatkan tanggal + waktu lengkap
- `toArray()` - Convert ke array
- `toJson()` - Convert ke JSON

---

### PriceListItem

| Property | Tipe | Keterangan |
|----------|------|------------|
| code | string | Kode produk |
| description | string | Deskripsi produk |
| product | string | Nama produk |
| category | string | Kategori |
| price | float | Harga |
| status | string | Status (1=tersedia) |

**Methods:**
- `isAvailable()` - Cek ketersediaan
- `getFormattedPrice()` - Format harga Rupiah
- `getCategoryShort()` - Nama kategori pendek
- `toArray()` - Convert ke array
- `toJson()` - Convert ke JSON

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

---

## Changelog

### Version 1.0.1
- Support Token PLN dengan SN panjang
- Support berbagai format failure reason
- Support format tanggal lengkap (DD/MM HH:mm)
- Perbaikan parsing balance dengan separator en-dash

### Version 1.0.0
- Initial release
- Parsing lengkap untuk transaksi, status check, webhook, dan price list

---

## License

MIT License

---

## Developer

**NdraDeveloper**

- GitHub: https://github.com/NdraDev
- Package: https://github.com/NdraDev/okeconnect-php-client
- Packagist: https://packagist.org/packages/ndradev/okeconnect-php-client

---

## Support

Jika ada pertanyaan atau masalah, silakan buat issue di:
https://github.com/NdraDev/okeconnect-php-client/issues
