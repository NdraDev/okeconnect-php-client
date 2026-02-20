<?php

namespace OkeConnect\Parsers;

use OkeConnect\Models\WebhookCallback;

class WebhookParser
{
    public function parse(string $message): WebhookCallback
    {
        $model = new WebhookCallback($message);

        if (stripos($message, 'SUKSES') !== false || stripos($message, 'SUKSES.') !== false) {
            $model->success = true;
            $model->status = 'SUKSES';
            $this->parseSuccess($message, $model);
            return $model;
        }

        if (stripos($message, 'GAGAL') !== false) {
            $model->success = false;
            $model->status = 'GAGAL';
            $this->parseFailed($message, $model);
            return $model;
        }

        $model->success = false;
        $model->status = 'UNKNOWN';
        $model->message = $message;
        return $model;
    }

    public function parseFromQuery(array $query): WebhookCallback
    {
        $message = $query['message'] ?? '';
        $refId = $query['refid'] ?? null;

        $model = $this->parse($message);

        if ($refId && !$model->refId) {
            $model->refId = $refId;
        }

        return $model;
    }

    private function parseSuccess(string $message, WebhookCallback $model): void
    {
        $model->message = $message;

        $this->extractTransactionId($message, $model);
        $this->extractRefId($message, $model);
        $this->extractProviderAndNominal($message, $model);
        $this->extractProductCodeAndDestination($message, $model);
        $this->extractSerialNumber($message, $model);
        $this->extractBalanceInfo($message, $model);
        $this->extractDateTime($message, $model);
    }

    private function parseFailed(string $message, WebhookCallback $model): void
    {
        $model->message = $message;

        $this->extractTransactionId($message, $model);
        $this->extractRefId($message, $model);
        $this->extractProviderAndNominal($message, $model);
        $this->extractProductCodeAndDestination($message, $model);
        $this->extractFailureReason($message, $model);
        $this->extractBalanceOnFailed($message, $model);
        $this->extractTime($message, $model);
    }

    private function extractTransactionId(string $message, WebhookCallback $model): void
    {
        if (preg_match('/T#(\d+)/', $message, $matches)) {
            $model->transactionId = $matches[1];
        }
    }

    private function extractRefId(string $message, WebhookCallback $model): void
    {
        if (preg_match('/R#(\d+)/', $message, $matches)) {
            $model->refId = $matches[1];
        }
    }

    private function extractProviderAndNominal(string $message, WebhookCallback $model): void
    {
        if (preg_match('/T#(?:\d+)\s+R#(?:\d+)\s+([A-Za-z\s]+?)\s+([\d\.]+)\s+[A-Z]+\d*\./', $message, $matches)) {
            $model->provider = trim($matches[1]);
            $model->nominal = $matches[2];
        }

        if (preg_match('/R#(?:\d+)\s+(H2H\s+[A-Za-z\s]+)\s+([\d\.]+)/', $message, $matches)) {
            $model->provider = trim($matches[1]);
            $model->nominal = $matches[2];
        }
    }

    private function extractProductCodeAndDestination(string $message, WebhookCallback $model): void
    {
        if (preg_match('/([A-Z]{2,}\d*)\.(\d{10,})/', $message, $matches)) {
            $model->productCode = $matches[1];
            $model->destination = $matches[2];
        }
    }

    private function extractSerialNumber(string $message, WebhookCallback $model): void
    {
        if (preg_match('/SN:\s*([^\n.]+(?:\/[^\n.]+)*)/i', $message, $matches)) {
            $model->serialNumber = trim($matches[1]);
        } elseif (preg_match('/SN:\s*([A-Z0-9\.]+)/i', $message, $matches)) {
            $model->serialNumber = $matches[1];
        }
    }

    private function extractBalanceInfo(string $message, WebhookCallback $model): void
    {
        if (preg_match('/Saldo\s+([\d\.]+)\s*[–-]\s*([\d\.]+)\s*=\s*([\d\.]+)/', $message, $matches)) {
            $model->balanceBefore = (float) str_replace('.', '', $matches[1]);
            $model->price = (float) str_replace('.', '', $matches[2]);
            $model->balanceAfter = (float) str_replace('.', '', $matches[3]);
        }
    }

    private function extractBalanceOnFailed(string $message, WebhookCallback $model): void
    {
        if (preg_match('/Saldo\s+([\d\.]+)\s+@/', $message, $matches)) {
            $model->balanceBefore = (float) str_replace('.', '', $matches[1]);
        }
    }

    private function extractDateTime(string $message, WebhookCallback $model): void
    {
        if (preg_match('/@(\d{2}\/\d{2}\s+\d{1,2}:\d{2})/', $message, $matches)) {
            $model->date = substr($matches[1], 0, 5);
            $model->time = substr($matches[1], 6);
        } elseif (preg_match('/@(\d{2}\/\d{2})\s+(\d{1,2}:\d{2})/', $message, $matches)) {
            $model->date = $matches[1];
            $model->time = $matches[2];
        } elseif (preg_match('/@(\d{1,2}:\d{2})/', $message, $matches)) {
            $model->time = $matches[1];
        }
    }

    private function extractTime(string $message, WebhookCallback $model): void
    {
        if (preg_match('/@(\d{1,2}:\d{2})/', $message, $matches)) {
            $model->time = $matches[1];
        }
    }

    private function extractFailureReason(string $message, WebhookCallback $model): void
    {
        if (preg_match('/GAGAL\.\s*(?:Ket:)?(.+?)\.\s*Saldo/i', $message, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(?:Ket:)?(.+?)\s+Saldo/i', $message, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(.+?)$/i', $message, $matches)) {
            $model->failureReason = trim($matches[1]);
        }
    }
}
