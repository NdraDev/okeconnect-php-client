<?php

namespace OkeConnect\Parsers;

use OkeConnect\Models\TransactionResponse;

class TransactionParser
{
    public function parse(string $response): TransactionResponse
    {
        $model = new TransactionResponse($response);
        $model->success = true;

        if (stripos($response, 'GAGAL') !== false) {
            $model->success = false;
            $model->status = 'FAILED';
            $this->parseFailed($response, $model);
            return $model;
        }

        if (stripos($response, 'SUKSES') !== false || stripos($response, 'SUKSES.') !== false) {
            $model->success = true;
            $model->status = 'SUCCESS';
            $this->parseSuccess($response, $model);
            return $model;
        }

        if (stripos($response, 'akan diproses') !== false) {
            $this->parseProcessing($response, $model);
            return $model;
        }

        $this->parseUnknown($response, $model);
        return $model;
    }

    private function parseProcessing(string $response, TransactionResponse $model): void
    {
        $model->status = 'PROCESSING';
        $model->message = $response;

        $this->extractTransactionId($response, $model);
        $this->extractRefId($response, $model);
        $this->extractProviderAndNominal($response, $model);
        $this->extractProductCodeAndDestination($response, $model);
        $this->extractBalanceInfo($response, $model);
        $this->extractTime($response, $model);
        $this->extractOpenDenomInfo($response, $model);
    }

    private function parseSuccess(string $response, TransactionResponse $model): void
    {
        $this->parseProcessing($response, $model);
        $model->status = 'SUCCESS';
        $this->extractSerialNumber($response, $model);
    }

    private function parseFailed(string $response, TransactionResponse $model): void
    {
        $this->parseProcessing($response, $model);
        $model->status = 'FAILED';
        $this->extractFailureReason($response, $model);
        $this->extractBalanceOnFailed($response, $model);
    }

    private function parseUnknown(string $response, TransactionResponse $model): void
    {
        $model->status = 'UNKNOWN';
        $model->message = $response;
        $this->extractTransactionId($response, $model);
        $this->extractRefId($response, $model);
    }

    private function extractTransactionId(string $response, TransactionResponse $model): void
    {
        if (preg_match('/T#(\d+)/', $response, $matches)) {
            $model->transactionId = $matches[1];
        }
    }

    private function extractRefId(string $response, TransactionResponse $model): void
    {
        if (preg_match('/R#(\d+)/', $response, $matches)) {
            $model->refId = $matches[1];
        }
    }

    private function extractProductCodeAndDestination(string $response, TransactionResponse $model): void
    {
        if (preg_match('/([A-Z]{2,}\d*)\.(\d{10,})/', $response, $matches)) {
            $model->productCode = $matches[1];
            $model->destination = $matches[2];
        }
    }

    private function extractProviderAndNominal(string $response, TransactionResponse $model): void
    {
        if (preg_match('/T#(?:\d+)\s+R#(?:\d+)\s+([A-Za-z\s]+?)\s+([\d\.]+(?:[A-Z]+)?)\s+[A-Z]+\d*\./', $response, $matches)) {
            $model->provider = trim($matches[1]);
            $model->nominal = $matches[2];
        }

        if (preg_match('/R#(?:\d+)\s+(H2H\s+[A-Za-z\s]+)\s+([\d\.]+)/', $response, $matches)) {
            $model->provider = trim($matches[1]);
            $model->nominal = $matches[2];
        }
    }

    private function extractOpenDenomInfo(string $response, TransactionResponse $model): void
    {
        if (preg_match('/QTY\s*:\s*(\d+)/', $response, $matches)) {
            $model->nominal = $matches[1];
        }

        if (preg_match('/H2H\s+([A-Z]+)\s+Topup\s+\(Bebas\s+Nominal\)/i', $response, $matches)) {
            $model->provider = trim($matches[1]);
        }
    }

    private function extractBalanceInfo(string $response, TransactionResponse $model): void
    {
        if (preg_match('/Saldo\s+([\d\.]+)\s*[–-]\s*([\d\.]+)\s*=\s*([\d\.]+)/', $response, $matches)) {
            $model->balanceBefore = (float) str_replace('.', '', $matches[1]);
            $model->price = (float) str_replace('.', '', $matches[2]);
            $model->balanceAfter = (float) str_replace('.', '', $matches[3]);
        }
    }

    private function extractBalanceOnFailed(string $response, TransactionResponse $model): void
    {
        if (preg_match('/Saldo\s+([\d\.]+)\s+@/', $response, $matches)) {
            $model->balanceBefore = (float) str_replace('.', '', $matches[1]);
        }
    }

    private function extractTime(string $response, TransactionResponse $model): void
    {
        if (preg_match('/@(\d{2}\/\d{2}\s+\d{1,2}:\d{2})/', $response, $matches)) {
            $model->time = $matches[1];
        } elseif (preg_match('/@(\d{1,2}:\d{2})/', $response, $matches)) {
            $model->time = $matches[1];
        }
    }

    private function extractSerialNumber(string $response, TransactionResponse $model): void
    {
        if (preg_match('/SN:\s*([^\n.]+(?:\/[^\n.]+)*)/i', $response, $matches)) {
            $model->serialNumber = trim($matches[1]);
        } elseif (preg_match('/SN:\s*([A-Z0-9\.]+)/i', $response, $matches)) {
            $model->serialNumber = $matches[1];
        }
    }

    private function extractFailureReason(string $response, TransactionResponse $model): void
    {
        if (preg_match('/GAGAL\.\s*(?:Ket:)?(.+?)\.\s*Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(?:Ket:)?(.+?)\.\s*Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(.+?)\.\s*Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(.+?)\s+Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL\.\s*(.+?)$/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        }
    }
}
