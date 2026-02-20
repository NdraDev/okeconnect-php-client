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

## Daftar Contoh Penggunaan

Tersedia 10 file contoh penggunaan lengkap di folder `examples/`:

| File | Deskripsi |
|------|-----------|
| `01_transaction.php` | Transaksi pulsa/data (fixed denom) |
| `02_transaction_open_denom.php` | Transaksi e-wallet (open denom/bebas nominal) |
| `03_check_status.php` | Cek status transaksi |
| `04_parse_webhook.php` | Parse webhook callback |
| `05_get_price_list.php` | Ambil semua daftar harga |
| `06_get_price.php` | Ambil harga satu produk |
| `07_find_product_by_code.php` | Cari produk berdasarkan kode |
| `08_find_product_by_category.php` | Cari produk berdasarkan kategori |
| `09_find_product_by_keterangan.php` | Cari produk berdasarkan keterangan |
| `10_find_product_by_status.php` | Cari produk berdasarkan status |
| `webhook.php` | Handler webhook untuk callback |

---

## Cara Penggunaan

### 1. Inisialisasi Client

```php
use OkeConnect\OkeConnect;

$oke = new OkeConnect(
    memberId: 'OK00123',
    pin: '123456',
    password: 'secret'
);

OkeConnect::setInstance($oke);
```

### 2. Menggunakan Helper Function

```php
oke_connect('OK00123', '123456', 'secret');

$response = oke_transaction('T1', '089660522887', 'REF123');
```

---

## Fitur Transaksi

### Transaksi Fixed Denom (Pulsa/Data)

```php
use OkeConnect\OkeConnectException;

try {
    $response = $oke->transaction('T1', '089660522887', 'TRX123');

    echo $response->transactionId;
    echo $response->refId;
    echo $response->provider;
    echo $response->nominal;
    echo $response->destination;

    if ($response->isSuccessful()) {
        echo "Berhasil";
        echo $response->serialNumber;
    }

    if ($response->isFailed()) {
        echo "Gagal: " . $response->failureReason;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage();
}
```

### Transaksi Open Denom (E-Wallet)

```php
try {
    $response = $oke->transactionOpenDenom('BBSDN', '085736044280', 50000, 'EW123');

    echo $response->nominal;
    echo $response->transactionId;

} catch (OkeConnectException $e) {
    if ($e->getCode() === OkeConnectException::INVALID_PARAMETER) {
        echo "Nominal harus antara 10.000 - 10.000.000";
    }
}
```

---

## Cek Status Transaksi

```php
try {
    $status = $oke->checkStatus('T5', '08980204060', 'REF123');

    echo $status->getStatusText();

    if ($status->isSuccessful()) {
        echo $status->serialNumber;
        echo $status->price;
    }

    if ($status->isFailed()) {
        echo $status->failureReason;
    }

    if ($status->isPending()) {
        echo "Transaksi masih pending";
    }

    if ($status->isNoData()) {
        echo "Data tidak ditemukan";
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage();
}
```

---

## Parse Webhook Callback

```php
try {
    $callback = $oke->parseWebhook($_GET);

    echo $callback->transactionId;
    echo $callback->refId;
    echo $callback->getStatusText();

    if ($callback->isSuccessful()) {
        echo $callback->serialNumber;
        echo $callback->date;
        echo $callback->time;
    }

    if ($callback->isFailed()) {
        echo $callback->failureReason;
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage();
}
```

---

## Price List dan Pencarian Produk

### Ambil Semua Price List

```php
try {
    $products = $oke->getPriceList();

    foreach ($products as $product) {
        echo $product->code;
        echo $product->description;
        echo $product->getFormattedPrice();

        if ($product->isAvailable()) {
            echo "Tersedia";
        }
    }

} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage();
}
```

### Cari Produk Berdasarkan Kode

```php
$produk = $oke->findProductByCode('SMDC150');
if ($produk) {
    echo $produk->description;
    echo $produk->getFormattedPrice();
}
```

### Cari Produk Berdasarkan Kategori

```php
$products = $oke->findProductByCategory('SMARTFREN');
foreach ($products as $product) {
    echo $product->code . ": " . $product->description;
}
```

### Cari Produk Berdasarkan Keterangan

```php
$products = $oke->findProductByKeterangan('30GB');
foreach ($products as $product) {
    echo $product->code . ": " . $product->description;
}
```

### Cari Produk Berdasarkan Status

```php
$available = $oke->findProductByStatus('1');
$unavailable = $oke->findProductByStatus('0');
```

### Ambil Harga Produk

```php
$harga = $oke->getPrice('SMDC150');
```

---

## Helper Functions

```php
$response = oke_transaction('T1', '089660522887', 'REF123');
$response = oke_topup('BBSDN', '085736044280', 50000, 'REF123');
$status = oke_check_status('T5', '08980204060', 'REF123');
$callback = oke_webhook($_GET);
$products = oke_products();
$product = oke_product('SMDC150');
$price = oke_price('SMDC150');
$product = oke_find_product_by_code('SMDC150');
$products = oke_find_product_by_category('SMARTFREN');
$products = oke_find_product_by_keterangan('30GB');
$products = oke_find_product_by_status('1');
```

---

## Webhook Handler

```php
<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

header('Content-Type: application/json');

if (empty($_GET['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message parameter']);
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

} catch (OkeConnectException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getUserMessage(),
    ]);
}
```

---

## Exception Handling

### OkeConnectException Codes

| Code | Constant | Keterangan |
|------|----------|------------|
| 1001 | MISSING_CREDENTIALS | Kredensial tidak lengkap |
| 1002 | INVALID_PARAMETER | Parameter tidak valid |
| 1003 | REQUEST_FAILED | Gagal connect ke server |
| 1004 | PARSE_ERROR | Gagal parse response |
| 1005 | TRANSACTION_FAILED | Transaksi gagal |
| 1006 | INSUFFICIENT_BALANCE | Saldo tidak cukup |
| 1007 | PRODUCT_NOT_FOUND | Produk tidak ditemukan |

### Cara Menggunakan Exception

```php
try {
    $response = $oke->transaction('T1', '089660522887', 'REF123');

} catch (OkeConnectException $e) {
    echo $e->getCode();
    echo $e->getUserMessage();
    echo $e->getMessage();
    print_r($e->getContext());

    if ($e->isCredentialError()) {
        echo "Periksa kredensial Anda";
    }

    if ($e->isTransactionError()) {
        echo "Transaksi gagal";
    }
}
```

---

## Validation

### Ref ID Validation
- Tidak boleh kosong
- Maksimal 50 karakter

### Phone Number Validation
- Tidak boleh kosong
- Harus 10-15 digit angka

### Qty Validation (Open Denom)
- Minimal 10.000
- Maksimal 10.000.000

---

## Response Models

### TransactionResponse

| Property | Tipe | Keterangan |
|----------|------|------------|
| transactionId | string|null | ID Transaksi |
| refId | string|null | ID Referensi |
| provider | string|null | Provider |
| nominal | string|null | Nominal |
| productCode | string|null | Kode Produk |
| destination | string|null | Nomor Tujuan |
| status | string|null | Status |
| time | string|null | Waktu |
| serialNumber | string|null | Serial Number |
| failureReason | string|null | Alasan Gagal |

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
| productCode | string|null | Kode Produk |
| destination | string|null | Nomor Tujuan |
| transactionTime | string|null | Waktu Transaksi |
| status | string|null | Status |
| serialNumber | string|null | Serial Number |
| price | float|null | Harga |
| failureReason | string|null | Alasan Gagal |
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
| productCode | string|null | Kode Produk |
| destination | string|null | Nomor Tujuan |
| status | string|null | Status |
| serialNumber | string|null | Serial Number |
| date | string|null | Tanggal |
| time | string|null | Waktu |
| failureReason | string|null | Alasan Gagal |

**Methods:**
- `isSuccessful()` - Cek apakah sukses
- `isFailed()` - Cek apakah gagal
- `getStatusText()` - Dapatkan teks status
- `getFullDateTime()` - Dapatkan tanggal + waktu
- `toArray()` - Convert ke array
- `toJson()` - Convert ke JSON

---

### PriceListItem

| Property | Tipe | Keterangan |
|----------|------|------------|
| code | string | Kode Produk |
| description | string | Deskripsi |
| product | string | Nama Produk |
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

Lihat [CHANGELOG.md](CHANGELOG.md) untuk riwayat perubahan lengkap.

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
