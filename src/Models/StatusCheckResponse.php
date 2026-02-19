<?php

namespace OkeConnect\Models;

class StatusCheckResponse
{
    public ?string $refId = null;
    public ?string $provider = null;
    public ?string $nominal = null;
    public ?string $productCode = null;
    public ?string $destination = null;
    public ?string $transactionTime = null;
    public ?string $status = null;
    public ?string $message = null;
    public ?string $serialNumber = null;
    public ?float $price = null;
    public ?string $failureReason = null;
    public ?string $transactionId = null;
    public string $rawResponse;
    public bool $success;

    public function __construct(string $rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    public function isSuccessful(): bool
    {
        return stripos($this->status ?? '', 'Sukses') !== false;
    }

    public function isFailed(): bool
    {
        return stripos($this->status ?? '', 'Gagal') !== false;
    }

    public function isPending(): bool
    {
        return stripos($this->status ?? '', 'Menunggu') !== false;
    }

    public function isNoData(): bool
    {
        return stripos($this->rawResponse, 'TIDAK ADA') !== false;
    }

    public function getStatusText(): string
    {
        if ($this->isSuccessful()) return 'Sukses';
        if ($this->isFailed()) return 'Gagal';
        if ($this->isPending()) return 'Pending';
        if ($this->isNoData()) return 'Tidak Ada Data';
        return 'Tidak Diketahui';
    }

    public function toArray(): array
    {
        return [
            'ref_id' => $this->refId,
            'provider' => $this->provider,
            'nominal' => $this->nominal,
            'product_code' => $this->productCode,
            'destination' => $this->destination,
            'transaction_time' => $this->transactionTime,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'serial_number' => $this->serialNumber,
            'price' => $this->price,
            'failure_reason' => $this->failureReason,
            'transaction_id' => $this->transactionId,
            'success' => $this->success,
            'is_successful' => $this->isSuccessful(),
            'is_failed' => $this->isFailed(),
            'is_pending' => $this->isPending(),
            'is_no_data' => $this->isNoData(),
            'message' => $this->message,
            'raw' => $this->rawResponse,
        ];
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
