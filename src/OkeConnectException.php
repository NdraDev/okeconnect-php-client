<?php

namespace OkeConnect;

use Exception;

class OkeConnectException extends Exception
{
    public const MISSING_CREDENTIALS = 1001;
    public const INVALID_PARAMETER = 1002;
    public const REQUEST_FAILED = 1003;
    public const PARSE_ERROR = 1004;
    public const TRANSACTION_FAILED = 1005;
    public const INSUFFICIENT_BALANCE = 1006;
    public const PRODUCT_NOT_FOUND = 1007;

    private array $context = [];

    public function __construct(string $message, int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function isCredentialError(): bool
    {
        return $this->code === self::MISSING_CREDENTIALS;
    }

    public function isTransactionError(): bool
    {
        return $this->code === self::TRANSACTION_FAILED || $this->code === self::INSUFFICIENT_BALANCE;
    }

    public function getUserMessage(): string
    {
        return match($this->code) {
            self::MISSING_CREDENTIALS => 'Kredensial tidak lengkap. Periksa konfigurasi Anda.',
            self::INVALID_PARAMETER => 'Parameter tidak valid.',
            self::REQUEST_FAILED => 'Gagal terhubung ke server OkeConnect.',
            self::PARSE_ERROR => 'Gagal memproses respons dari server.',
            self::TRANSACTION_FAILED => 'Transaksi gagal.',
            self::INSUFFICIENT_BALANCE => 'Saldo tidak mencukupi.',
            self::PRODUCT_NOT_FOUND => 'Produk tidak ditemukan.',
            default => $this->message,
        };
    }
}
