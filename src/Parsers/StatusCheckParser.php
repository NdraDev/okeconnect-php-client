<?php

namespace OkeConnect\Parsers;

use OkeConnect\Models\StatusCheckResponse;

class StatusCheckParser
{
    public function parse(string $response): StatusCheckResponse
    {
        $model = new StatusCheckResponse($response);

        if (stripos($response, 'TIDAK ADA') !== false) {
            $model->success = false;
            $model->status = 'NO_DATA';
            $this->parseNoData($response, $model);
            return $model;
        }

        if (stripos($response, 'Mhn tunggu') !== false || stripos($response, 'Menunggu') !== false) {
            $model->success = true;
            $model->status = 'PENDING';
            $this->parsePending($response, $model);
            return $model;
        }

        if (preg_match('/status\s+Sukses/i', $response) || stripos($response, 'SUKSES') !== false) {
            $model->success = true;
            $model->status = 'SUCCESS';
            $this->parseStatusDetail($response, $model);
            return $model;
        }

        if (preg_match('/status\s+Gagal/i', $response) || stripos($response, 'GAGAL') !== false) {
            $model->success = false;
            $model->status = 'FAILED';
            $this->parseStatusDetail($response, $model);
            return $model;
        }

        $model->success = false;
        $model->status = 'UNKNOWN';
        $model->message = $response;
        return $model;
    }

    private function parseStatusDetail(string $response, StatusCheckResponse $model): void
    {
        $model->message = $response;

        $this->extractRefId($response, $model);
        $this->extractProviderAndNominal($response, $model);
        $this->extractProductCodeAndDestination($response, $model);
        $this->extractTransactionTime($response, $model);
        $this->extractSerialNumber($response, $model);
        $this->extractPrice($response, $model);

        if ($model->status === 'FAILED') {
            $this->extractFailureReason($response, $model);
        }
    }

    private function parsePending(string $response, StatusCheckResponse $model): void
    {
        $model->message = $response;

        if (preg_match('/T#(\d+)/', $response, $matches)) {
            $model->transactionId = $matches[1];
        }

        $this->extractRefId($response, $model);
        $this->extractProductCodeAndDestination($response, $model);

        if (preg_match('/@(\d{1,2}:\d{2})/', $response, $matches)) {
            $model->transactionTime = $matches[1];
        }
    }

    private function parseNoData(string $response, StatusCheckResponse $model): void
    {
        $model->message = $response;

        if (preg_match('/Tujuan\s+(\d+)/', $response, $matches)) {
            $model->destination = $matches[1];
        }

        if (preg_match('/tgl\s+(\d{2}\/\d{2}\/\d{4})/', $response, $matches)) {
            $model->transactionTime = $matches[1];
        }
    }

    private function extractRefId(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/R#(\d+)/', $response, $matches)) {
            $model->refId = $matches[1];
        }
    }

    private function extractProviderAndNominal(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/R#(?:\d+)\s+([A-Za-z\s]+?)\s+([\d\.]+)\s+[A-Z]+\d+\./', $response, $matches)) {
            $model->provider = trim($matches[1]);
            $model->nominal = $matches[2];
        }
    }

    private function extractProductCodeAndDestination(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/([A-Z]{2,}\d*)\.(\d{10,})/', $response, $matches)) {
            $model->productCode = $matches[1];
            $model->destination = $matches[2];
        }
    }

    private function extractTransactionTime(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/jam\s+(\d{1,2}:\d{2})/', $response, $matches)) {
            $model->transactionTime = $matches[1];
        } elseif (preg_match('/@(\d{1,2}:\d{2})/', $response, $matches)) {
            $model->transactionTime = $matches[1];
        }
    }

    private function extractSerialNumber(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/SN:\s*([^\n.]+(?:\/[^\n.]+)*)/i', $response, $matches)) {
            $model->serialNumber = trim($matches[1]);
        } elseif (preg_match('/SN:\s*([A-Z0-9\.]+)/i', $response, $matches)) {
            $model->serialNumber = $matches[1];
        }
    }

    private function extractPrice(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/Hrg\s+([\d\.]+)/i', $response, $matches)) {
            $model->price = (float) str_replace('.', '', $matches[1]);
        }
    }

    private function extractFailureReason(string $response, StatusCheckResponse $model): void
    {
        if (preg_match('/status\s+Gagal[.\s]+(.+?)\.\s*Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL[.\s]+(.+?)\.\s*Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        } elseif (preg_match('/GAGAL[.\s]+(.+?)\s+Saldo/i', $response, $matches)) {
            $model->failureReason = trim($matches[1]);
        }
    }
}
