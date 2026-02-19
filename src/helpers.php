<?php

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
