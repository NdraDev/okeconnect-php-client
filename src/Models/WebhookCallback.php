<?php

namespace OkeConnect\Models;

class WebhookCallback
{
    public ?string $transactionId = null;
    public ?string $refId = null;
    public ?string $provider = null;
    public ?string $nominal = null;
    public ?string $productCode = null;
    public ?string $destination = null;
    public ?string $status = null;
    public ?string $message = null;
    public ?string $serialNumber = null;
    public ?float $price = null;
    public ?string $date = null;
    public ?string $time = null;
    public ?string $failureReason = null;
    public string $rawResponse;
    public bool $success;

    public function __construct(string $rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    public function isSuccessful(): bool
    {
        return stripos($this->status ?? '', 'SUKSES') !== false;
    }

    public function isFailed(): bool
    {
        return stripos($this->status ?? '', 'GAGAL') !== false;
    }

    public function getStatusText(): string
    {
        if ($this->isSuccessful()) return 'Sukses';
        if ($this->isFailed()) return 'Gagal';
        return 'Tidak Diketahui';
    }

    public function getFullDateTime(): ?string
    {
        if ($this->date && $this->time) {
            return $this->date . ' ' . $this->time;
        }
        return $this->time;
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
            'serial_number' => $this->serialNumber,
            'price' => $this->price,
            'date' => $this->date,
            'time' => $this->time,
            'datetime' => $this->getFullDateTime(),
            'failure_reason' => $this->failureReason,
            'success' => $this->success,
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
