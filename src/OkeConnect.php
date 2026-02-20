<?php

namespace OkeConnect;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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

    private static ?OkeConnect $instance = null;
    private Client $http;
    private string $memberId;
    private string $pin;
    private string $password;
    private string $priceListId;

    private TransactionParser $transactionParser;
    private StatusCheckParser $statusCheckParser;
    private WebhookParser $webhookParser;
    private PriceListParser $priceListParser;

    public function __construct(string $memberId, string $pin, string $password, string $priceListId = '905ccd028329b0a')
    {
        $this->validateCredentials($memberId, $pin, $password);

        $this->memberId = $memberId;
        $this->pin = $pin;
        $this->password = $password;
        $this->priceListId = $priceListId;

        $this->http = new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);

        $this->transactionParser = new TransactionParser();
        $this->statusCheckParser = new StatusCheckParser();
        $this->webhookParser = new WebhookParser();
        $this->priceListParser = new PriceListParser();
    }

    public static function getInstance(): OkeConnect
    {
        if (self::$instance === null) {
            throw new OkeConnectException(
                'Instance belum diinisialisasi. Gunakan new OkeConnect() atau setInstance()',
                OkeConnectException::MISSING_CREDENTIALS
            );
        }
        return self::$instance;
    }

    public static function setInstance(OkeConnect $instance): void
    {
        self::$instance = $instance;
    }

    public static function transaction(string $product, string $destination, string $refId): TransactionResponse
    {
        return self::getInstance()->transaction($product, $destination, $refId);
    }

    public static function transactionOpenDenom(string $product, string $destination, int $qty, string $refId): TransactionResponse
    {
        return self::getInstance()->transactionOpenDenom($product, $destination, $qty, $refId);
    }

    public static function checkStatus(string $product, string $destination, string $refId, ?int $qty = null): StatusCheckResponse
    {
        return self::getInstance()->checkStatus($product, $destination, $refId, $qty);
    }

    public static function parseWebhook(array $query): WebhookCallback
    {
        return self::getInstance()->parseWebhook($query);
    }

    public static function parseWebhookMessage(string $message): WebhookCallback
    {
        return self::getInstance()->parseWebhookMessage($message);
    }

    public static function getPriceList(): array
    {
        return self::getInstance()->getPriceList();
    }

    public static function getPriceListByCategory(string $category): array
    {
        return self::getInstance()->getPriceListByCategory($category);
    }

    public static function findProductByCode(string $code): ?PriceListItem
    {
        return self::getInstance()->findProductByCode($code);
    }

    public static function findProductByCategory(string $category): array
    {
        return self::getInstance()->findProductByCategory($category);
    }

    public static function findProductByKeterangan(string $keyword): array
    {
        return self::getInstance()->findProductByKeterangan($keyword);
    }

    public static function findProductByStatus(string $status): array
    {
        return self::getInstance()->findProductByStatus($status);
    }

    public static function getAvailableProducts(): array
    {
        return self::getInstance()->getAvailableProducts();
    }

    public static function getPrice(string $code): ?float
    {
        return self::getInstance()->getPrice($code);
    }

    public function transaction(string $product, string $destination, string $refId): TransactionResponse
    {
        $this->validateRefId($refId);
        $this->validatePhoneNumber($destination);

        try {
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

            $body = (string) $response->getBody();

            return $this->transactionParser->parse($body);

        } catch (RequestException $e) {
            throw new OkeConnectException(
                'Gagal melakukan transaksi: ' . $e->getMessage(),
                OkeConnectException::REQUEST_FAILED,
                $e,
                ['product' => $product, 'destination' => $destination, 'refId' => $refId]
            );
        }
    }

    public function transactionOpenDenom(string $product, string $destination, int $qty, string $refId): TransactionResponse
    {
        $this->validateRefId($refId);
        $this->validatePhoneNumber($destination);
        $this->validateQty($qty);

        try {
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

            $body = (string) $response->getBody();

            return $this->transactionParser->parse($body);

        } catch (RequestException $e) {
            throw new OkeConnectException(
                'Gagal melakukan transaksi: ' . $e->getMessage(),
                OkeConnectException::REQUEST_FAILED,
                $e,
                ['product' => $product, 'destination' => $destination, 'qty' => $qty, 'refId' => $refId]
            );
        }
    }

    public function checkStatus(string $product, string $destination, string $refId, ?int $qty = null): StatusCheckResponse
    {
        $this->validateRefId($refId);
        $this->validatePhoneNumber($destination);

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

        try {
            $response = $this->http->get('/trx', ['query' => $query]);
            $body = (string) $response->getBody();
            return $this->statusCheckParser->parse($body);

        } catch (RequestException $e) {
            throw new OkeConnectException(
                'Gagal cek status: ' . $e->getMessage(),
                OkeConnectException::REQUEST_FAILED,
                $e,
                ['product' => $product, 'refId' => $refId]
            );
        }
    }

    public function parseWebhook(array $query): WebhookCallback
    {
        if (empty($query['message'])) {
            throw new OkeConnectException(
                'Parameter message tidak boleh kosong',
                OkeConnectException::INVALID_PARAMETER,
                null,
                ['query' => $query]
            );
        }

        return $this->webhookParser->parseFromQuery($query);
    }

    public function parseWebhookMessage(string $message): WebhookCallback
    {
        if (empty($message)) {
            throw new OkeConnectException(
                'Message tidak boleh kosong',
                OkeConnectException::INVALID_PARAMETER
            );
        }

        return $this->webhookParser->parse($message);
    }

    public function getPriceList(): array
    {
        try {
            $response = $this->http->get(self::PRICE_URL, [
                'query' => ['id' => $this->priceListId]
            ]);

            $body = (string) $response->getBody();
            return $this->priceListParser->parse($body);

        } catch (RequestException $e) {
            throw new OkeConnectException(
                'Gagal mengambil price list: ' . $e->getMessage(),
                OkeConnectException::REQUEST_FAILED,
                $e
            );
        }
    }

    public function getPriceListByCategory(string $category): array
    {
        return $this->priceListParser->parseByCategory(
            $this->getPriceList(),
            $category
        );
    }

    public function findProductByCode(string $code): ?PriceListItem
    {
        $items = $this->getPriceList();
        return $this->priceListParser->findByCodeFromArray($items, $code);
    }

    public function findProductByCategory(string $category): array
    {
        $items = $this->getPriceList();
        return array_filter($items, function (PriceListItem $item) use ($category) {
            return stripos($item->category, $category) !== false;
        });
    }

    public function findProductByKeterangan(string $keyword): array
    {
        $items = $this->getPriceList();
        return array_filter($items, function (PriceListItem $item) use ($keyword) {
            return stripos($item->description, $keyword) !== false;
        });
    }

    public function findProductByStatus(string $status): array
    {
        $items = $this->getPriceList();
        return array_filter($items, function (PriceListItem $item) use ($status) {
            return $item->status === $status;
        });
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

    private function validateCredentials(string $memberId, string $pin, string $password): void
    {
        if (empty($memberId)) {
            throw new OkeConnectException(
                'Member ID tidak boleh kosong',
                OkeConnectException::MISSING_CREDENTIALS
            );
        }

        if (empty($pin)) {
            throw new OkeConnectException(
                'PIN tidak boleh kosong',
                OkeConnectException::MISSING_CREDENTIALS
            );
        }

        if (empty($password)) {
            throw new OkeConnectException(
                'Password tidak boleh kosong',
                OkeConnectException::MISSING_CREDENTIALS
            );
        }
    }

    private function validateRefId(string $refId): void
    {
        if (empty($refId)) {
            throw new OkeConnectException(
                'Ref ID tidak boleh kosong',
                OkeConnectException::INVALID_PARAMETER
            );
        }

        if (strlen($refId) > 50) {
            throw new OkeConnectException(
                'Ref ID terlalu panjang (max 50 karakter)',
                OkeConnectException::INVALID_PARAMETER
            );
        }
    }

    private function validatePhoneNumber(string $phone): void
    {
        if (empty($phone)) {
            throw new OkeConnectException(
                'Nomor telepon tidak boleh kosong',
                OkeConnectException::INVALID_PARAMETER
            );
        }

        if (!preg_match('/^\d{10,15}$/', $phone)) {
            throw new OkeConnectException(
                'Format nomor telepon tidak valid (harus 10-15 digit angka)',
                OkeConnectException::INVALID_PARAMETER,
                null,
                ['phone' => $phone]
            );
        }
    }

    private function validateQty(int $qty): void
    {
        if ($qty < 10000) {
            throw new OkeConnectException(
                'Nominal minimal 10.000',
                OkeConnectException::INVALID_PARAMETER,
                null,
                ['qty' => $qty]
            );
        }

        if ($qty > 10000000) {
            throw new OkeConnectException(
                'Nominal maksimal 10.000.000',
                OkeConnectException::INVALID_PARAMETER,
                null,
                ['qty' => $qty]
            );
        }
    }
}
