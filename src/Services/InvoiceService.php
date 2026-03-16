<?php

namespace ZirveDonusum\Services;

/**
 * E-Fatura / E-Arşiv Fatura İşlemleri
 *
 * Endpoint'ler /cp/{accountId}/einvoice/... ve /cp/{accountId}/earchive/... altında.
 * Sen portaldeki fatura sayfalarının Network tab'ını paylaştıkça
 * doğru endpoint'ler buraya eklenecek.
 */
class InvoiceService extends BaseService
{
    // ─── E-Fatura Listeleme ──────────────────────────────────────────

    /**
     * Gelen e-faturaları listele
     */
    public function listIncoming(array $filters = []): array
    {
        // TODO: Network tab'dan doğru endpoint gelecek
        return $this->http->get($this->cp('einvoice/GetIncomingInvoices'), $filters);
    }

    /**
     * Giden e-faturaları listele
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->get($this->cp('einvoice/GetOutgoingInvoices'), $filters);
    }

    // ─── E-Arşiv ─────────────────────────────────────────────────────

    /**
     * E-Arşiv faturaları listele
     */
    public function listArchive(array $filters = []): array
    {
        return $this->http->get($this->cp('earchive/GetArchiveInvoices'), $filters);
    }

    // ─── Fatura Detay ────────────────────────────────────────────────

    /**
     * Tek fatura detayı getir
     */
    public function get(string $invoiceId): array
    {
        return $this->http->get($this->cp('einvoice/GetInvoiceDetail'), ['id' => $invoiceId]);
    }

    /**
     * Fatura HTML görüntüsü
     */
    public function getHtml(string $invoiceId): string
    {
        return $this->http->download($this->cp('einvoice/GetInvoiceHtml'), ['id' => $invoiceId]);
    }

    /**
     * Fatura XML'i indir
     */
    public function downloadXml(string $invoiceId): string
    {
        return $this->http->download($this->cp('einvoice/DownloadXml'), ['id' => $invoiceId]);
    }

    /**
     * Fatura PDF'i indir
     */
    public function downloadPdf(string $invoiceId): string
    {
        return $this->http->download($this->cp('einvoice/DownloadPdf'), ['id' => $invoiceId]);
    }

    // ─── Fatura Gönderme ─────────────────────────────────────────────

    /**
     * Yeni e-fatura gönder
     */
    public function send(array $invoiceData): array
    {
        return $this->http->post($this->cp('einvoice/SendInvoice'), $invoiceData);
    }

    /**
     * E-Arşiv fatura oluştur
     */
    public function createArchive(array $invoiceData): array
    {
        return $this->http->post($this->cp('earchive/CreateInvoice'), $invoiceData);
    }

    // ─── Fatura İşlemleri ────────────────────────────────────────────

    /**
     * Gelen faturayı kabul et
     */
    public function accept(string $invoiceId): array
    {
        return $this->http->postForm($this->cp('einvoice/AcceptInvoice'), ['id' => $invoiceId]);
    }

    /**
     * Gelen faturayı reddet
     */
    public function reject(string $invoiceId, string $reason = ''): array
    {
        return $this->http->postForm($this->cp('einvoice/RejectInvoice'), [
            'id' => $invoiceId,
            'reason' => $reason,
        ]);
    }

    /**
     * Fatura durumunu sorgula
     */
    public function status(string $invoiceId): array
    {
        return $this->http->get($this->cp('einvoice/GetInvoiceStatus'), ['id' => $invoiceId]);
    }
}
