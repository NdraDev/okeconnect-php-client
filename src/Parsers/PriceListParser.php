<?php

namespace OkeConnect\Parsers;

use OkeConnect\Models\PriceListItem;

class PriceListParser
{
    public function parse(string $json): array
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Expected JSON array of price items');
        }

        $items = [];
        foreach ($data as $item) {
            $items[] = new PriceListItem($item);
        }

        return $items;
    }

    public function parseByCategory(string $json, string $category): array
    {
        $items = $this->parse($json);
        return array_filter($items, function (PriceListItem $item) use ($category) {
            return stripos($item->category, $category) !== false;
        });
    }

    public function parseByProduct(string $json, string $productName): array
    {
        $items = $this->parse($json);
        return array_filter($items, function (PriceListItem $item) use ($productName) {
            return stripos($item->product, $productName) !== false;
        });
    }

    public function parseByDescription(string $json, string $keyword): array
    {
        $items = $this->parse($json);
        return array_filter($items, function (PriceListItem $item) use ($keyword) {
            return stripos($item->description, $keyword) !== false;
        });
    }

    public function findByCode(string $json, string $code): ?PriceListItem
    {
        $items = $this->parse($json);
        foreach ($items as $item) {
            if (strtoupper($item->code) === strtoupper($code)) {
                return $item;
            }
        }
        return null;
    }

    public function findManyByCode(string $json, array $codes): array
    {
        $items = $this->parse($json);
        $codes = array_map('strtoupper', $codes);
        return array_filter($items, function (PriceListItem $item) use ($codes) {
            return in_array(strtoupper($item->code), $codes);
        });
    }

    public function getAvailable(array $items): array
    {
        return array_filter($items, function (PriceListItem $item) {
            return $item->isAvailable();
        });
    }

    public function getUnavailable(array $items): array
    {
        return array_filter($items, function (PriceListItem $item) {
            return !$item->isAvailable();
        });
    }

    public function sortByPrice(array $items, string $order = 'asc'): array
    {
        usort($items, function ($a, $b) use ($order) {
            if ($order === 'desc') {
                return $b->price <=> $a->price;
            }
            return $a->price <=> $b->price;
        });
        return $items;
    }

    public function groupByCategory(array $items): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->category][] = $item;
        }
        return $grouped;
    }

    public function groupByProduct(array $items): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->product][] = $item;
        }
        return $grouped;
    }

    public function toArray(array $items): array
    {
        return array_map(function (PriceListItem $item) {
            return $item->toArray();
        }, $items);
    }

    public function toJson(array $items, int $options = 0): string
    {
        return json_encode($this->toArray($items), $options);
    }

    public function getPriceRange(array $items): array
    {
        $prices = array_map(function (PriceListItem $item) {
            return $item->price;
        }, $items);

        return [
            'min' => min($prices),
            'max' => max($prices),
            'avg' => count($prices) > 0 ? array_sum($prices) / count($prices) : 0,
        ];
    }

    public function filterByPriceRange(array $items, float $min, float $max): array
    {
        return array_filter($items, function (PriceListItem $item) use ($min, $max) {
            return $item->price >= $min && $item->price <= $max;
        });
    }

    public function countByCategory(array $items): array
    {
        $counts = [];
        foreach ($items as $item) {
            $counts[$item->category] = ($counts[$item->category] ?? 0) + 1;
        }
        return $counts;
    }
}
