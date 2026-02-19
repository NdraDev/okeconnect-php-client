<?php

namespace OkeConnect\Tests;

use PHPUnit\Framework\TestCase;
use OkeConnect\Parsers\TransactionParser;
use OkeConnect\Parsers\StatusCheckParser;
use OkeConnect\Parsers\WebhookParser;
use OkeConnect\Parsers\PriceListParser;

class ParserTest extends TestCase
{
    public function testTransactionProcessing(): void
    {
        $parser = new TransactionParser();
        $response = $parser->parse('T#210286229 R#113 Three 1.000 T1.089660522887 akan diproses. Saldo 279.655 - 1.321 = 278.334 @19:08');

        $this->assertEquals('210286229', $response->transactionId);
        $this->assertEquals('113', $response->refId);
        $this->assertEquals('Three', $response->provider);
        $this->assertEquals('1.000', $response->nominal);
        $this->assertEquals('T1', $response->productCode);
        $this->assertEquals('089660522887', $response->destination);
        $this->assertTrue($response->isProcessing());
    }

    public function testTransactionSuccess(): void
    {
        $parser = new TransactionParser();
        $response = $parser->parse('T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11');

        $this->assertEquals('210288912', $response->transactionId);
        $this->assertEquals('R230512.1911.2100F1', $response->serialNumber);
        $this->assertTrue($response->isSuccessful());
    }

    public function testTransactionFailed(): void
    {
        $parser = new TransactionParser();
        $response = $parser->parse('T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo 10.795.667 @22:15');

        $this->assertEquals('41169572', $response->transactionId);
        $this->assertEquals('Nomor tujuan salah', $response->failureReason);
        $this->assertTrue($response->isFailed());
    }

    public function testStatusCheckSuccess(): void
    {
        $parser = new StatusCheckParser();
        $response = $parser->parse('R#999 Three 5.000 T5.08980204060 sudah pernah jam 18:46, status Sukses. SN: R25042218462100b7. Hrg 6.487');

        $this->assertEquals('999', $response->refId);
        $this->assertEquals('Three', $response->provider);
        $this->assertEquals('5.000', $response->nominal);
        $this->assertEquals('18:46', $response->transactionTime);
        $this->assertEquals('R25042218462100b7', $response->serialNumber);
        $this->assertEquals(6487.0, $response->price);
        $this->assertTrue($response->isSuccessful());
    }

    public function testStatusCheckFailed(): void
    {
        $parser = new StatusCheckParser();
        $response = $parser->parse('R#999 Three 5.000 T5.08980204060 sudah pernah jam 18:46, status Gagal. Mohon diperiksa kembali No tujuan.');

        $this->assertTrue($response->isFailed());
        $this->assertStringContainsString('Mohon diperiksa kembali No tujuan', $response->failureReason);
    }

    public function testStatusCheckPending(): void
    {
        $parser = new StatusCheckParser();
        $response = $parser->parse('Mhn tunggu trx sblmnya selesai: T#762221212 R#999 T5.08980204060 @18:46, status Menunggu Jawaban.');

        $this->assertEquals('762221212', $response->transactionId);
        $this->assertEquals('999', $response->refId);
        $this->assertTrue($response->isPending());
    }

    public function testStatusCheckNoData(): void
    {
        $parser = new StatusCheckParser();
        $response = $parser->parse('TIDAK ADA transaksi Tujuan 08980204060 pada tgl 22/04/2025. Tidak ada data.');

        $this->assertEquals('08980204060', $response->destination);
        $this->assertTrue($response->isNoData());
    }

    public function testWebhookSuccess(): void
    {
        $parser = new WebhookParser();
        $response = $parser->parse('T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11');

        $this->assertEquals('210288912', $response->transactionId);
        $this->assertEquals('114', $response->refId);
        $this->assertEquals('R230512.1911.2100F1', $response->serialNumber);
        $this->assertEquals('12/05', $response->date);
        $this->assertEquals('19:11', $response->time);
        $this->assertTrue($response->isSuccessful());
    }

    public function testWebhookFailed(): void
    {
        $parser = new WebhookParser();
        $response = $parser->parse('T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo 10.795.667 @22:15');

        $this->assertEquals('41169572', $response->transactionId);
        $this->assertEquals('1235', $response->refId);
        $this->assertEquals('Nomor tujuan salah', $response->failureReason);
        $this->assertTrue($response->isFailed());
    }

    public function testWebhookFromQuery(): void
    {
        $parser = new WebhookParser();
        $response = $parser->parseFromQuery([
            'refid' => '114',
            'message' => 'T#210288912 R#114 Three 1.000 T1.089660522887 SUKSES. SN: R230512.1911.2100F1. Saldo 278.334 - 1.321 = 277.013 @12/05 19:11'
        ]);

        $this->assertEquals('114', $response->refId);
        $this->assertTrue($response->isSuccessful());
    }

    public function testPriceListParse(): void
    {
        $parser = new PriceListParser();
        $json = '[{"kode":"SMDC150","keterangan":"Smart 30GB","produk":"Data Smart Combo","kategori":"KUOTA SMARTFREN","harga":"134600","status":"1"}]';

        $items = $parser->parse($json);

        $this->assertCount(1, $items);
        $this->assertEquals('SMDC150', $items[0]->code);
        $this->assertEquals(134600.0, $items[0]->price);
        $this->assertTrue($items[0]->isAvailable());
    }

    public function testPriceListFindByCode(): void
    {
        $parser = new PriceListParser();
        $json = '[{"kode":"SMDC150","keterangan":"Smart 30GB","produk":"Data Smart Combo","kategori":"KUOTA SMARTFREN","harga":"134600","status":"1"}]';

        $product = $parser->findByCode($json, 'SMDC150');

        $this->assertNotNull($product);
        $this->assertEquals('SMDC150', $product->code);
    }
}
