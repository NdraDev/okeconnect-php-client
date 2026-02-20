# OkeConnect PHP Client

Package PHP untuk integrasi API OkeConnect H2H dengan parsing lengkap untuk semua response.

**Requirement:** PHP 8.2 atau lebih baru

**Developer:** NdraDeveloper

**Versi:** 4.0.0

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

**Request:**
```php
use OkeConnect\OkeConnectException;

try {
    $response = $oke->transaction('T1', '089660522887', 'TRX123');
    
    echo "Transaction ID: " . $response->transactionId . PHP_EOL;
    echo "Ref ID: " . $response->refId . PHP_EOL;
    echo "Provider: " . $response->provider . PHP_EOL;
    echo "Nominal: " . $response->nominal . PHP_EOL;
    echo "Destination: " . $response->destination . PHP_EOL;
    echo "Price: Rp " . number_format($response->price, 0, ',', '.') . PHP_EOL;
    
    if ($response->isSuccessful()) {
        echo "Serial Number: " . $response->serialNumber . PHP_EOL;
    }
    
    if ($response->isFailed()) {
        echo "Failure Reason: " . $response->failureReason . PHP_EOL;
    }
    
} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
```

**Contoh Response Sukses:**
```
T#210286229 R#113 Three 1.000 T1.089660522887 akan diproses. Saldo 279.655 - 1.321 = 278.334 @19:08
```

**Response yang diparsing:**
```php
[
    'transaction_id' => '210286229',
    'ref_id' => '113',
    'provider' => 'Three',
    'nominal' => '1.000',
    'product_code' => 'T1',
    'destination' => '089660522887',
    'status' => 'PROCESSING',
    'status_text' => 'Diproses',
    'price' => 1321.0,
    'time' => '19:08',
    'success' => true,
]
```

**Contoh Response Sukses dengan SN:**
```
T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11
```

**Contoh Response Gagal:**
```
T#373620355 R#604528 Three 15.000 T15.089620308676 GAGAL. Mohon diperiksa kembali No tujuan sebelum di ulang. Saldo 1.352.242 @22:20
```

---

### Transaksi Open Denom (E-Wallet)

**Request:**
```php
try {
    $response = $oke->transactionOpenDenom('BBSDN', '085736044280', 50000, 'EW123');
    
    echo "Transaction ID: " . $response->transactionId . PHP_EOL;
    echo "Ref ID: " . $response->refId . PHP_EOL;
    echo "Provider: " . $response->provider . PHP_EOL;
    echo "Nominal (Qty): " . $response->nominal . PHP_EOL;
    echo "Destination: " . $response->destination . PHP_EOL;
    echo "Price: Rp " . number_format($response->price, 0, ',', '.') . PHP_EOL;
    
    if ($response->isSuccessful()) {
        echo "Serial Number: " . $response->serialNumber . PHP_EOL;
    }
    
    if ($response->isFailed()) {
        echo "Failure Reason: " . $response->failureReason . PHP_EOL;
    }
    
} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
    
    if ($e->getCode() === OkeConnectException::INVALID_PARAMETER) {
        echo "Nominal harus antara 10.000 - 10.000.000" . PHP_EOL;
    }
}
```

**Contoh Request API:**
```
GET https://h2h.okeconnect.com/trx?product=BBSDN&dest=085736044280&qty=50000&refID=EW123&memberID=OK00123&pin=123456&password=secret
```

**Contoh Response Sukses:**
```
T#762261897 R#7777 H2H DANA Topup (Bebas Nominal) BBSDN.085736044280 , QTY : 50000 akan diproses. Saldo 43.928.256 - 12.516 = 43.915.740 @19:14
```

**Response yang diparsing:**
```php
[
    'transaction_id' => '762261897',
    'ref_id' => '7777',
    'provider' => 'DANA',
    'nominal' => '50000',
    'product_code' => 'BBSDN',
    'destination' => '085736044280',
    'status' => 'PROCESSING',
    'status_text' => 'Diproses',
    'price' => 12516.0,
    'time' => '19:14',
    'success' => true,
]
```

---

## Cek Status Transaksi

**Request:**
```php
try {
    $status = $oke->checkStatus('T5', '08980204060', 'REF123');
    
    echo "Ref ID: " . $status->refId . PHP_EOL;
    echo "Provider: " . $status->provider . PHP_EOL;
    echo "Nominal: " . $status->nominal . PHP_EOL;
    echo "Destination: " . $status->destination . PHP_EOL;
    echo "Status: " . $status->getStatusText() . PHP_EOL;
    
    if ($status->isSuccessful()) {
        echo "Serial Number: " . $status->serialNumber . PHP_EOL;
        echo "Transaction Time: " . $status->transactionTime . PHP_EOL;
        echo "Price: Rp " . number_format($status->price, 0, ',', '.') . PHP_EOL;
    }
    
    if ($status->isFailed()) {
        echo "Failure Reason: " . $status->failureReason . PHP_EOL;
    }
    
    if ($status->isPending()) {
        echo "Transaksi masih pending" . PHP_EOL;
    }
    
    if ($status->isNoData()) {
        echo "Data tidak ditemukan" . PHP_EOL;
    }
    
} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
```

**Contoh Request API:**
```
GET https://h2h.okeconnect.com/trx?product=T5&dest=08980204060&refID=REF123&memberID=OK00123&pin=123456&password=secret&check=1
```

**Contoh Response Sukses:**
```
R#999 Three 5.000 T5.08980204060 sudah pernah jam 18:46, status Sukses. SN: R25042218462100b7. Hrg 6.487
```

**Response yang diparsing:**
```php
[
    'ref_id' => '999',
    'provider' => 'Three',
    'nominal' => '5.000',
    'product_code' => 'T5',
    'destination' => '08980204060',
    'transaction_time' => '18:46',
    'status' => 'SUCCESS',
    'status_text' => 'Sukses',
    'serial_number' => 'R25042218462100b7',
    'price' => 6487.0,
    'success' => true,
]
```

**Contoh Response Pending:**
```
Mhn tunggu trx sblmnya selesai: T#762221212 R#999 T5.08980204060 @18:46, status Menunggu Jawaban.
```

**Contoh Response No Data:**
```
TIDAK ADA transaksi Tujuan 08980204060 pada tgl 22/04/2025. Tidak ada data.
```

---

## Parse Webhook Callback

**Request:**
```php
try {
    $callback = $oke->parseWebhook($_GET);
    
    echo "Transaction ID: " . $callback->transactionId . PHP_EOL;
    echo "Ref ID: " . $callback->refId . PHP_EOL;
    echo "Provider: " . $callback->provider . PHP_EOL;
    echo "Nominal: " . $callback->nominal . PHP_EOL;
    echo "Destination: " . $callback->destination . PHP_EOL;
    echo "Status: " . $callback->getStatusText() . PHP_EOL;
    echo "Price: Rp " . number_format($callback->price, 0, ',', '.') . PHP_EOL;
    
    if ($callback->isSuccessful()) {
        echo "Serial Number: " . $callback->serialNumber . PHP_EOL;
        echo "Date: " . $callback->date . PHP_EOL;
        echo "Time: " . $callback->time . PHP_EOL;
        echo "Full DateTime: " . $callback->getFullDateTime() . PHP_EOL;
    }
    
    if ($callback->isFailed()) {
        echo "Failure Reason: " . $callback->failureReason . PHP_EOL;
    }
    
} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
```

**Format Callback dari OkeConnect:**
```
{url_callback}?refid=114&message=ISI_PESAN
```

**Contoh Callback Sukses:**
```
T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11
```

**Response yang diparsing:**
```php
[
    'transaction_id' => '210288912',
    'ref_id' => '114',
    'provider' => 'Three',
    'nominal' => '1.000',
    'product_code' => 'T1',
    'destination' => '089660522887',
    'status' => 'SUKSES',
    'status_text' => 'Sukses',
    'serial_number' => 'R230512.1911.2100F1',
    'price' => 1321.0,
    'date' => '12/05',
    'time' => '19:11',
    'datetime' => '12/05 19:11',
    'success' => true,
]
```

**Contoh Callback Gagal:**
```
T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo 10.795.667 @22:15
```

---

## Price List dan Pencarian Produk

### Ambil Semua Price List

**Request:**
```php
try {
    $products = $oke->getPriceList();
    
    echo "Total Produk: " . count($products) . PHP_EOL;
    
    foreach ($products as $product) {
        echo "Kode: " . $product->code . PHP_EOL;
        echo "Deskripsi: " . $product->description . PHP_EOL;
        echo "Produk: " . $product->product . PHP_EOL;
        echo "Kategori: " . $product->category . PHP_EOL;
        echo "Harga: " . $product->getFormattedPrice() . PHP_EOL;
        echo "Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . PHP_EOL;
        echo "---" . PHP_EOL;
    }
    
} catch (OkeConnectException $e) {
    echo "Error: " . $e->getUserMessage() . PHP_EOL;
}
```

**Contoh Request API:**
```
GET https://okeconnect.com/harga/json?id=905ccd028329b0a
```

**Contoh Response JSON:**
```json
[
    {
        "kode": "SMDC150",
        "keterangan": "Smart 30GB All + 60GB (01-05) 30 Hari",
        "produk": "Data Smart Combo",
        "kategori": "KUOTA SMARTFREN",
        "harga": "134600",
        "status": "1"
    },
    {
        "kode": "SMDC10",
        "keterangan": "Smart 2GB All + 2GB (01-05) + 2GB Chat 7 Hari",
        "produk": "Data Smart Combo",
        "kategori": "KUOTA SMARTFREN",
        "harga": "15050",
        "status": "1"
    }
]
```

**Response yang diparsing:**
```php
[
    [
        'code' => 'SMDC150',
        'description' => 'Smart 30GB All + 60GB (01-05) 30 Hari',
        'product' => 'Data Smart Combo',
        'category' => 'KUOTA SMARTFREN',
        'price' => 134600.0,
        'status' => '1',
        'is_available' => true,
    ],
    // ...
]
```

---

### Cari Produk Berdasarkan Kode

**Request:**
```php
$product = $oke->findProductByCode('SMDC150');

if ($product) {
    echo "Kode: " . $product->code . PHP_EOL;
    echo "Deskripsi: " . $product->description . PHP_EOL;
    echo "Kategori: " . $product->category . PHP_EOL;
    echo "Harga: " . $product->getFormattedPrice() . PHP_EOL;
    echo "Status: " . ($product->isAvailable() ? 'Tersedia' : 'Tidak Tersedia') . PHP_EOL;
} else {
    echo "Produk tidak ditemukan" . PHP_EOL;
}
```

**Response:**
```php
[
    'code' => 'SMDC150',
    'description' => 'Smart 30GB All + 60GB (01-05) 30 Hari',
    'product' => 'Data Smart Combo',
    'category' => 'KUOTA SMARTFREN',
    'price' => 134600.0,
    'status' => '1',
    'is_available' => true,
]
```

---

### Cari Produk Berdasarkan Kategori

**Request:**
```php
$products = $oke->findProductByCategory('SMARTFREN');

echo "Total Produk: " . count($products) . PHP_EOL;

foreach ($products as $product) {
    echo $product->code . ": " . $product->description . PHP_EOL;
    echo "Harga: " . $product->getFormattedPrice() . PHP_EOL;
}
```

**Response:**
```php
[
    [
        'code' => 'SMDC150',
        'description' => 'Smart 30GB All + 60GB (01-05) 30 Hari',
        'category' => 'KUOTA SMARTFREN',
        'price' => 134600.0,
        'is_available' => true,
    ],
    // ...
]
```

---

### Cari Produk Berdasarkan Keterangan

**Request:**
```php
$products = $oke->findProductByKeterangan('30GB');

foreach ($products as $product) {
    echo $product->code . ": " . $product->description . PHP_EOL;
}
```

---

### Cari Produk Berdasarkan Status

**Request:**
```php
// Produk tersedia (status = 1)
$available = $oke->findProductByStatus('1');
echo "Produk Tersedia: " . count($available) . PHP_EOL;

// Produk tidak tersedia (status = 0)
$unavailable = $oke->findProductByStatus('0');
echo "Produk Tidak Tersedia: " . count($unavailable) . PHP_EOL;
```

---

### Ambil Harga Produk

**Request:**
```php
$price = $oke->getPrice('SMDC150');

if ($price !== null) {
    echo "Harga: Rp " . number_format($price, 0, ',', '.') . PHP_EOL;
} else {
    echo "Produk tidak ditemukan" . PHP_EOL;
}
```

**Response:**
```
Harga: Rp 134.600
```

---

## Helper Functions

```php
// Inisialisasi
oke_connect('OK00123', '123456', 'secret');

// Transaksi
$response = oke_transaction('T1', '089660522887', 'REF123');
$response = oke_topup('BBSDN', '085736044280', 50000, 'REF123');

// Cek Status
$status = oke_check_status('T5', '08980204060', 'REF123');

// Webhook
$callback = oke_webhook($_GET);

// Price List
$products = oke_products();
$product = oke_product('SMDC150');
$price = oke_price('SMDC150');

// Pencarian Produk
$product = oke_find_product_by_code('SMDC150');
$products = oke_find_product_by_category('SMARTFREN');
$products = oke_find_product_by_keterangan('30GB');
$products = oke_find_product_by_status('1');
```

---

## Webhook Handler

**File: `examples/webhook.php`**

```php
<?php

require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;
use OkeConnect\OkeConnectException;

header('Content-Type: application/json');

if (empty($_GET['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter message tidak boleh kosong']);
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
            'destination' => $callback->destination,
            'price' => $callback->price,
        ]);
    } elseif ($callback->isFailed()) {
        echo json_encode([
            'success' => false,
            'ref_id' => $callback->refId,
            'transaction_id' => $callback->transactionId,
            'failure_reason' => $callback->failureReason,
            'destination' => $callback->destination,
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
    echo "Code: " . $e->getCode() . PHP_EOL;
    echo "Message: " . $e->getMessage() . PHP_EOL;
    echo "User Message: " . $e->getUserMessage() . PHP_EOL;
    print_r($e->getContext());

    if ($e->isCredentialError()) {
        echo "Periksa kredensial Anda" . PHP_EOL;
    }

    if ($e->isTransactionError()) {
        echo "Transaksi gagal" . PHP_EOL;
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
| price | float|null | Harga |
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
| price | float|null | Harga |
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
