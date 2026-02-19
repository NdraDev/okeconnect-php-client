# OkeConnect PHP Client

Package PHP untuk integrasi API OkeConnect H2H dengan parsing lengkap untuk semua response.

**Requirement:** PHP 8.2 atau lebih baru

## Instalasi

```bash
composer require ndradev/okeconnect-php-client
```

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

### 2. Transaksi Pulsa (Fixed Denom)

```php
$response = $oke->transaction('T1', '089660522887', 'TRX123');

echo $response->transactionId;
echo $response->refId;
echo $response->provider;
echo $response->nominal;
echo $response->productCode;
echo $response->destination;
echo $response->status;
echo $response->balanceBefore;
echo $response->price;
echo $response->balanceAfter;
echo $response->time;
echo $response->serialNumber;
echo $response->failureReason;

if ($response->isProcessing()) {
    echo "Sedang diproses";
}

if ($response->isSuccessful()) {
    echo "Berhasil";
}

if ($response->isFailed()) {
    echo "Gagal";
}

echo $response->getStatusText();
print_r($response->toArray());
echo $response->toJson();
```

### 3. Transaksi E-Wallet (Open Denom)

```php
$response = $oke->transactionOpenDenom('BBSDN', '085736044280', 50000, 'EW123');

echo $response->nominal;
echo $response->transactionId;
```

### 4. Cek Status Transaksi

```php
$status = $oke->checkStatus('T5', '08980204060', 'REF123');

echo $status->refId;
echo $status->provider;
echo $status->nominal;
echo $status->productCode;
echo $status->destination;
echo $status->transactionTime;
echo $status->status;
echo $status->serialNumber;
echo $status->price;
echo $status->failureReason;
echo $status->transactionId;

if ($status->isSuccessful()) {
    echo "Transaksi sukses";
}

if ($status->isFailed()) {
    echo "Transaksi gagal";
}

if ($status->isPending()) {
    echo "Transaksi pending";
}

if ($status->isNoData()) {
    echo "Data tidak ditemukan";
}

echo $status->getStatusText();
print_r($status->toArray());
```

### 5. Parse Webhook Callback

```php
$callback = $oke->parseWebhook($_GET);

echo $callback->transactionId;
echo $callback->refId;
echo $callback->provider;
echo $callback->nominal;
echo $callback->productCode;
echo $callback->destination;
echo $callback->status;
echo $callback->serialNumber;
echo $callback->balanceBefore;
echo $callback->price;
echo $callback->balanceAfter;
echo $callback->date;
echo $callback->time;
echo $callback->getFullDateTime();
echo $callback->failureReason;

if ($callback->isSuccessful()) {
    echo "Webhook sukses";
}

if ($callback->isFailed()) {
    echo "Webhook gagal";
}

echo $callback->getStatusText();
print_r($callback->toArray());
```

### 6. Price List

```php
$products = $oke->getPriceList();
foreach ($products as $product) {
    echo $product->code;
    echo $product->description;
    echo $product->product;
    echo $product->category;
    echo $product->price;
    echo $product->getFormattedPrice();
    echo $product->status;
    
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

$found = $oke->findProductsByCode(['T1', 'T5', 'S10']);
```

## Helper Functions

```php
$response = oke_transaction('T1', '089660522887', 'REF123');
$response = oke_topup('BBSDN', '085736044280', 50000, 'REF123');
$status = oke_check_status('T5', '08980204060', 'REF123');
$callback = oke_webhook($_GET);
$products = oke_products();
$product = oke_product('SMDC150');
$price = oke_price('SMDC150');
```

## Contoh Webhook Handler

```php
<?php
require_once 'vendor/autoload.php';

use OkeConnect\OkeConnect;

header('Content-Type: application/json');

$oke = new OkeConnect('OK00123', '123456', 'secret');
$callback = $oke->parseWebhook($_GET);

if ($callback->isSuccessful()) {
    // Update database
    // Send notification
    
    echo json_encode([
        'success' => true,
        'ref_id' => $callback->refId,
        'transaction_id' => $callback->transactionId,
        'serial_number' => $callback->serialNumber,
    ]);
}

if ($callback->isFailed()) {
    // Update database
    // Handle failure
    
    echo json_encode([
        'success' => false,
        'ref_id' => $callback->refId,
        'failure_reason' => $callback->failureReason,
    ]);
}
```

## Response Models

### TransactionResponse
- transactionId, refId, provider, nominal, productCode, destination
- status, statusText, balanceBefore, price, balanceAfter, time
- serialNumber (jika sukses), failureReason (jika gagal), message
- isProcessing(), isSuccessful(), isFailed(), getStatusText()
- toArray(), toJson()

### StatusCheckResponse
- refId, provider, nominal, productCode, destination
- transactionTime, status, statusText, serialNumber, price
- failureReason, transactionId, message
- isSuccessful(), isFailed(), isPending(), isNoData(), getStatusText()
- toArray(), toJson()

### WebhookCallback
- transactionId, refId, provider, nominal, productCode, destination
- status, statusText, serialNumber, balanceBefore, price, balanceAfter
- date, time, datetime (getFullDateTime()), failureReason, message
- isSuccessful(), isFailed(), getStatusText()
- toArray(), toJson()

### PriceListItem
- code, description, product, category, categoryShort
- price, formattedPrice (getFormattedPrice()), status
- isAvailable(), toArray(), toJson()

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
