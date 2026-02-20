<?php

namespace OkeConnect\Models;

class PriceListItem
{
    public string $code;
    public string $description;
    public string $product;
    public string $category;
    public float $price;
    public string $status;

    public function __construct(array $data)
    {
        $this->code = $data['kode'] ?? '';
        $this->description = $data['keterangan'] ?? '';
        $this->product = $data['produk'] ?? '';
        $this->category = $data['kategori'] ?? '';
        $this->price = (float) ($data['harga'] ?? 0);
        $this->status = $data['status'] ?? '0';
    }

    public function isAvailable(): bool
    {
        return $this->status === '1';
    }

    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getCategoryShort(): string
    {
        $parts = explode(' ', $this->category);
        return end($parts);
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'product' => $this->product,
            'category' => $this->category,
            'category_short' => $this->getCategoryShort(),
            'price' => $this->price,
            'formatted_price' => $this->getFormattedPrice(),
            'status' => $this->status,
            'is_available' => $this->isAvailable(),
        ];
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
