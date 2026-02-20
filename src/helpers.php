<?php

if (!function_exists('oke_connect')) {
    function oke_connect(string $memberId, string $pin, string $password, string $priceListId = '905ccd028329b0a'): \OkeConnect\OkeConnect
    {
        $instance = new \OkeConnect\OkeConnect($memberId, $pin, $password, $priceListId);
        \OkeConnect\OkeConnect::setInstance($instance);
        return $instance;
    }
}

if (!function_exists('oke_transaction')) {
    function oke_transaction(string $product, string $destination, string $refId): \OkeConnect\Models\TransactionResponse
    {
        return \OkeConnect\OkeConnect::transaction($product, $destination, $refId);
    }
}

if (!function_exists('oke_topup')) {
    function oke_topup(string $product, string $destination, int $amount, string $refId): \OkeConnect\Models\TransactionResponse
    {
        return \OkeConnect\OkeConnect::transactionOpenDenom($product, $destination, $amount, $refId);
    }
}

if (!function_exists('oke_check_status')) {
    function oke_check_status(string $product, string $destination, string $refId, ?int $qty = null): \OkeConnect\Models\StatusCheckResponse
    {
        return \OkeConnect\OkeConnect::checkStatus($product, $destination, $refId, $qty);
    }
}

if (!function_exists('oke_webhook')) {
    function oke_webhook(array $query): \OkeConnect\Models\WebhookCallback
    {
        return \OkeConnect\OkeConnect::parseWebhook($query);
    }
}

if (!function_exists('oke_products')) {
    function oke_products(): array
    {
        return \OkeConnect\OkeConnect::getPriceList();
    }
}

if (!function_exists('oke_product')) {
    function oke_product(string $code): ?\OkeConnect\Models\PriceListItem
    {
        return \OkeConnect\OkeConnect::findProductByCode($code);
    }
}

if (!function_exists('oke_price')) {
    function oke_price(string $code): ?float
    {
        return \OkeConnect\OkeConnect::getPrice($code);
    }
}

if (!function_exists('oke_cek_saldo')) {
    function oke_cek_saldo(string $product, string $destination, string $refId): ?float
    {
        $response = \OkeConnect\OkeConnect::transaction($product, $destination, $refId);
        return $response->balanceBefore;
    }
}

if (!function_exists('oke_find_product_by_code')) {
    function oke_find_product_by_code(string $code): ?\OkeConnect\Models\PriceListItem
    {
        return \OkeConnect\OkeConnect::findProductByCode($code);
    }
}

if (!function_exists('oke_find_product_by_category')) {
    function oke_find_product_by_category(string $category): array
    {
        return \OkeConnect\OkeConnect::findProductByCategory($category);
    }
}

if (!function_exists('oke_find_product_by_keterangan')) {
    function oke_find_product_by_keterangan(string $keyword): array
    {
        return \OkeConnect\OkeConnect::findProductByKeterangan($keyword);
    }
}

if (!function_exists('oke_find_product_by_status')) {
    function oke_find_product_by_status(string $status): array
    {
        return \OkeConnect\OkeConnect::findProductByStatus($status);
    }
}
