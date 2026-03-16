<?php

namespace ZirveDonusum\Services;

/**
 * E-Fatura / E-Arşiv Fatura İşlemleri
 *
 * Gerçek endpoint'ler (Network tab'dan):
 *   GET /cp/{accountId}/newInvoice/getOptions
 *   GET /cp/{accountId}/newInvoice/get/?invoiceType=EInvoice
 *   GET /cp/{accountId}/newInvoice/getSubtotals
 *   GET /cp/{accountId}/DocumentNo/GenerateOrValidateSerial?uuid=...&prefix=EFAB
 *   GET /cp/{accountId}/packagecode/getall
 *   GET /cp/{accountId}/paymenttype/getall
 *   GET /cp/{accountId}/paymentcode/getall
 *   GET /cp/{accountId}/Nace/GetAccountVatRates
 */
class InvoiceService extends BaseService
{
    // ─── Yeni Fatura Oluşturma ───────────────────────────────────────

    /**
     * Fatura oluşturma seçeneklerini getir (KDV oranları, para birimleri, birimler vb.)
     */
    public function getOptions(): array
    {
        return $this->http->get($this->cp('newInvoice/getOptions'));
    }

    /**
     * Yeni fatura formu / taslak getir
     *
     * @param string $invoiceType EInvoice, EArchive, vb.
     */
    public function getNewInvoice(string $invoiceType = 'EInvoice'): array
    {
        return $this->http->get($this->cp('newInvoice/get/'), ['invoiceType' => $invoiceType]);
    }

    /**
     * Fatura alt toplamlarını hesapla
     */
    public function getSubtotals(): array
    {
        return $this->http->get($this->cp('newInvoice/getSubtotals'));
    }

    /**
     * Belge numarası üret veya doğrula
     *
     * @param string $uuid Fatura UUID
     * @param string $prefix Seri prefix (EFAB, EARB vb.)
     */
    public function generateDocumentNo(string $uuid, string $prefix = 'EFAB'): array
    {
        return $this->http->get($this->cp('DocumentNo/GenerateOrValidateSerial'), [
            'uuid' => $uuid,
            'prefix' => $prefix,
        ]);
    }

    // ─── Referans Verileri ───────────────────────────────────────────

    /**
     * Paket kodlarını getir
     */
    public function getPackageCodes(): array
    {
        return $this->http->get($this->cp('packagecode/getall'));
    }

    /**
     * Ödeme tiplerini getir
     */
    public function getPaymentTypes(): array
    {
        return $this->http->get($this->cp('paymenttype/getall'));
    }

    /**
     * Ödeme kodlarını getir
     */
    public function getPaymentCodes(): array
    {
        return $this->http->get($this->cp('paymentcode/getall'));
    }

    /**
     * Hesabın KDV oranlarını getir (NACE koduna göre)
     */
    public function getVatRates(): array
    {
        return $this->http->get($this->cp('Nace/GetAccountVatRates'));
    }

    // ─── Fatura Listeleme ────────────────────────────────────────────

    /**
     * Gelen e-faturaları listele
     * TODO: Gelen fatura listesi sayfasının endpoint'i Network tab'dan eklenecek
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->get($this->cp('einvoice/GetIncomingInvoices'), $filters);
    }

    /**
     * Giden e-faturaları listele
     * TODO: Giden fatura listesi sayfasının endpoint'i Network tab'dan eklenecek
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->get($this->cp('einvoice/GetOutgoingInvoices'), $filters);
    }

    /**
     * E-Arşiv faturaları listele
     * TODO: E-Arşiv listesi sayfasının endpoint'i Network tab'dan eklenecek
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
     * Yeni e-fatura gönder / kaydet
     */
    public function send(array $invoiceData): array
    {
        return $this->http->post($this->cp('newInvoice/save'), $invoiceData);
    }

    /**
     * E-Arşiv fatura oluştur
     */
    public function createArchive(array $invoiceData): array
    {
        return $this->http->post($this->cp('newInvoice/save'), array_merge($invoiceData, [
            'invoiceType' => 'EArchive',
        ]));
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
