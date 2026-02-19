<?php

namespace OkeConnect;

use GuzzleHttp\Client;
use OkeConnect\Parsers\TransactionParser;
use OkeConnect\Parsers\StatusCheckParser;
use OkeConnect\Parsers\WebhookParser;
use OkeConnect\Parsers\PriceListParser;
use OkeConnect\Models\TransactionResponse;
use OkeConnect\Models\StatusCheckResponse;
use OkeConnect\Models\WebhookCallback;
use OkeConnect\Models\PriceListItem;

class OkeConnect
{
    private const BASE_URL = 'https://h2h.okeconnect.com';
    private const PRICE_URL = 'https://okeconnect.com/harga/json';

    private Client $http;
    private string $memberId;
    private string $pin;
    private string $password;
    private string $priceListId;

    private TransactionParser $transactionParser;
    private StatusCheckParser $statusCheckParser;
    private WebhookParser $webhookParser;
    private PriceListParser $priceListParser;

    private ?string $lastRawResponse = null;
    private ?string $lastRawRequest = null;

    public function __construct(string $memberId, string $pin, string $password, string $priceListId = '905ccd028329b0a')
    {
        $this->memberId = $memberId;
        $this->pin = $pin;
        $this->password = $password;
        $this->priceListId = $priceListId;

        $this->http = new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        $this->transactionParser = new TransactionParser();
        $this->statusCheckParser = new StatusCheckParser();
        $this->webhookParser = new WebhookParser();
        $this->priceListParser = new PriceListParser();
    }

    public function transaction(string $product, string $destination, string $refId): TransactionResponse
    {
        $this->lastRawRequest = http_build_query([
            'memberID' => $this->memberId,
            'product' => $product,
            'dest' => $destination,
            'refID' => $refId,
            'pin' => $this->pin,
            'password' => $this->password,
        ]);

        $response = $this->http->get('/trx', [
            'query' => [
                'memberID' => $this->memberId,
                'product' => $product,
                'dest' => $destination,
                'refID' => $refId,
                'pin' => $this->pin,
                'password' => $this->password,
            ]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->transactionParser->parse($this->lastRawResponse);
    }

    public function transactionOpenDenom(string $product, string $destination, int $qty, string $refId): TransactionResponse
    {
        $this->lastRawRequest = http_build_query([
            'memberID' => $this->memberId,
            'product' => $product,
            'dest' => $destination,
            'qty' => $qty,
            'refID' => $refId,
            'pin' => $this->pin,
            'password' => $this->password,
        ]);

        $response = $this->http->get('/trx', [
            'query' => [
                'memberID' => $this->memberId,
                'product' => $product,
                'dest' => $destination,
                'qty' => $qty,
                'refID' => $refId,
                'pin' => $this->pin,
                'password' => $this->password,
            ]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->transactionParser->parse($this->lastRawResponse);
    }

    public function checkStatus(string $product, string $destination, string $refId, ?int $qty = null): StatusCheckResponse
    {
        $query = [
            'memberID' => $this->memberId,
            'product' => $product,
            'dest' => $destination,
            'refID' => $refId,
            'pin' => $this->pin,
            'password' => $this->password,
            'check' => 1,
        ];

        if ($qty !== null) {
            $query['qty'] = $qty;
        }

        $this->lastRawRequest = http_build_query($query);

        $response = $this->http->get('/trx', ['query' => $query]);
        $this->lastRawResponse = (string) $response->getBody();
        return $this->statusCheckParser->parse($this->lastRawResponse);
    }

    public function parseWebhook(array $query): WebhookCallback
    {
        return $this->webhookParser->parseFromQuery($query);
    }

    public function parseWebhookMessage(string $message): WebhookCallback
    {
        return $this->webhookParser->parse($message);
    }

    public function getPriceList(): array
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->priceListParser->parse($this->lastRawResponse);
    }

    public function getPriceListRaw(): string
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->lastRawResponse;
    }

    public function getPriceListByCategory(string $category): array
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->priceListParser->parseByCategory($this->lastRawResponse, $category);
    }

    public function getPriceListByProduct(string $productName): array
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->priceListParser->parseByProduct($this->lastRawResponse, $productName);
    }

    public function findProductByCode(string $code): ?PriceListItem
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->priceListParser->findByCode($this->lastRawResponse, $code);
    }

    public function findProductsByCode(array $codes): array
    {
        $response = $this->http->get(self::PRICE_URL, [
            'query' => ['id' => $this->priceListId]
        ]);

        $this->lastRawResponse = (string) $response->getBody();
        return $this->priceListParser->findManyByCode($this->lastRawResponse, $codes);
    }

    public function getAvailableProducts(): array
    {
        $items = $this->getPriceList();
        return $this->priceListParser->getAvailable($items);
    }

    public function getPrice(string $code): ?float
    {
        $product = $this->findProductByCode($code);
        return $product?->price;
    }

    public function getLastRawResponse(): ?string
    {
        return $this->lastRawResponse;
    }

    public function getLastRawRequest(): ?string
    {
        return $this->lastRawRequest;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function getTransactionParser(): TransactionParser
    {
        return $this->transactionParser;
    }

    public function getStatusCheckParser(): StatusCheckParser
    {
        return $this->statusCheckParser;
    }

    public function getWebhookParser(): WebhookParser
    {
        return $this->webhookParser;
    }

    public function getPriceListParser(): PriceListParser
    {
        return $this->priceListParser;
    }
}
