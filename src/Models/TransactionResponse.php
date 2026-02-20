<?php

namespace OkeConnect\Models;

class TransactionResponse
{
    public ?string $transactionId = null;
    public ?string $refId = null;
    public ?string $provider = null;
    public ?string $nominal = null;
    public ?string $productCode = null;
    public ?string $destination = null;
    public ?string $status = null;
    public ?string $message = null;
    public ?string $time = null;
    public ?string $serialNumber = null;
    public ?string $failureReason = null;
    public string $rawResponse;
    public bool $success;

    public function __construct(string $rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    public function isProcessing(): bool
    {
        return stripos($this->rawResponse, 'akan diproses') !== false;
    }

    public function isSuccessful(): bool
    {
        return stripos($this->rawResponse, 'SUKSES') !== false;
    }

    public function isFailed(): bool
    {
        return stripos($this->rawResponse, 'GAGAL') !== false;
    }

    public function getStatusText(): string
    {
        if ($this->isSuccessful()) return 'Berhasil';
        if ($this->isFailed()) return 'Gagal';
        if ($this->isProcessing()) return 'Diproses';
        return 'Tidak Diketahui';
    }

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'ref_id' => $this->refId,
            'provider' => $this->provider,
            'nominal' => $this->nominal,
            'product_code' => $this->productCode,
            'destination' => $this->destination,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'time' => $this->time,
            'serial_number' => $this->serialNumber,
            'failure_reason' => $this->failureReason,
            'success' => $this->success,
            'is_processing' => $this->isProcessing(),
            'is_successful' => $this->isSuccessful(),
            'is_failed' => $this->isFailed(),
            'message' => $this->message,
            'raw' => $this->rawResponse,
        ];
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
