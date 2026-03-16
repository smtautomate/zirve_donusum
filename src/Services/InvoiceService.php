<?php

namespace ZirveDonusum\Services;

/**
 * E-Fatura / E-Arşiv Fatura İşlemleri
 *
 * Endpoint'ler sen Network tab'dan paylaştıkça güncellenecek.
 * Şu anki path'ler eMikro'nun tipik yapısına göre tahmin edildi.
 */
class InvoiceService extends BaseService
{
    // ─── Fatura Listeleme ────────────────────────────────────────────

    /**
     * Gelen e-faturaları listele
     */
    public function listIncoming(array $filters = []): array
    {
        // TODO: Doğru endpoint Network tab'dan gelecek
        return $this->http->get('/einvoice/getIncomingInvoices', $filters);
    }

    /**
     * Giden e-faturaları listele
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->get('/einvoice/getOutgoingInvoices', $filters);
    }

    /**
     * E-Arşiv faturaları listele
     */
    public function listArchive(array $filters = []): array
    {
        return $this->http->get('/earchive/getArchiveInvoices', $filters);
    }

    // ─── Fatura Detay ────────────────────────────────────────────────

    /**
     * Tek fatura detayı getir
     */
    public function get(string $invoiceId): array
    {
        return $this->http->get('/einvoice/getInvoiceDetail', ['id' => $invoiceId]);
    }

    /**
     * Fatura HTML görüntüsü
     */
    public function getHtml(string $invoiceId): string
    {
        return $this->http->download('/einvoice/getInvoiceHtml', ['id' => $invoiceId]);
    }

    /**
     * Fatura XML'i indir
     */
    public function downloadXml(string $invoiceId): string
    {
        return $this->http->download('/einvoice/downloadXml', ['id' => $invoiceId]);
    }

    /**
     * Fatura PDF'i indir
     */
    public function downloadPdf(string $invoiceId): string
    {
        return $this->http->download('/einvoice/downloadPdf', ['id' => $invoiceId]);
    }

    // ─── Fatura Gönderme ─────────────────────────────────────────────

    /**
     * Yeni e-fatura gönder
     */
    public function send(array $invoiceData): array
    {
        return $this->http->post('/einvoice/sendInvoice', $invoiceData);
    }

    /**
     * E-Arşiv fatura oluştur
     */
    public function createArchive(array $invoiceData): array
    {
        return $this->http->post('/earchive/createInvoice', $invoiceData);
    }

    // ─── Fatura İşlemleri ────────────────────────────────────────────

    /**
     * Gelen faturayı kabul et
     */
    public function accept(string $invoiceId): array
    {
        return $this->http->postForm('/einvoice/acceptInvoice', ['id' => $invoiceId]);
    }

    /**
     * Gelen faturayı reddet
     */
    public function reject(string $invoiceId, string $reason = ''): array
    {
        return $this->http->postForm('/einvoice/rejectInvoice', [
            'id' => $invoiceId,
            'reason' => $reason,
        ]);
    }

    /**
     * Fatura durumunu sorgula
     */
    public function status(string $invoiceId): array
    {
        return $this->http->get('/einvoice/getInvoiceStatus', ['id' => $invoiceId]);
    }
}
