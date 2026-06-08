<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Fatura servisi.
 * API: POST /api/BasicIntegrationApi
 * Referans: iuyum_api/Bilgi Sistemleri/E-Fatura&E-Arsiv/RestAPI/Request/
 */
class EInvoiceService extends BaseService
{
    /**
     * VKN/TCKN ile mukellefin e-Fatura kullanicisi olup olmadigini sorgular.
     */
    public function isUser(string $vknTckn): array
    {
        return $this->http->action('IsEInvoiceUser', [
            'vknTckn' => $vknTckn,
        ]);
    }

    /**
     * E-Fatura sistemi kullanicisi (mukellef) listesi.
     */
    public function getUsers(array $filters = []): array
    {
        return $this->http->action('GetEInvoiceUsers', $filters);
    }

    /**
     * Bir mukellefin e-Fatura sistemindeki alias (takma ad) listesi.
     */
    public function getUserAliases(string $vknTckn): array
    {
        return $this->http->action('GetUserAliasses', [
            'vknTckn' => $vknTckn,
        ]);
    }

    /**
     * E-Fatura gonder (tek veya coklu UBL-TR fatura).
     *
     * @param  array $invoices  Her eleman {"Invoice": {...}, "Scenario": 0, ...} yapısında
     */
    public function send(array $invoices): array
    {
        return $this->http->action('SendInvoice', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Gelen e-Fatura listesi.
     *
     * @param  array $filters  startDate, endDate, limit vb.
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->action('GetInboxInvoiceList', $filters);
    }

    /**
     * Giden e-Fatura listesi.
     *
     * @param  array $filters  startDate, endDate, limit vb.
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->action('GetOutboxInvoiceList', $filters);
    }

    /**
     * Gelen bir e-Fatura detayi (UUID ile).
     */
    public function getIncoming(string $uuid): array
    {
        return $this->http->action('GetInboxInvoiceView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Giden bir e-Fatura detayi (UUID ile).
     */
    public function getOutgoing(string $uuid): array
    {
        return $this->http->action('GetOutboxInvoiceView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Fatura PDF gorunumu iste.
     *
     * @param  string $uuid
     * @param  int    $type  0=EFatura, 1=EArsiv
     */
    public function getPdf(string $uuid, int $type = 0): array
    {
        return $this->http->action('GetPdfViewRequest', [
            'uuid' => $uuid,
            'type' => $type,
        ]);
    }

    /**
     * Taslak fatura gonder (draft'i yayimla).
     */
    public function sendDraft(string $uuid): array
    {
        return $this->http->action('SendDraft', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Taslak faturay iptal et.
     */
    public function cancelDraft(string $uuid): array
    {
        return $this->http->action('CancelDraft', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Gonderimi basarisiz faturalari yeniden gonder.
     *
     * @param  array $uuids  UUID listesi
     */
    public function retry(array $uuids): array
    {
        return $this->http->action('RetrySendInvoices', [
            'uuids' => $uuids,
        ]);
    }

    /**
     * Faturaları "alinmis" olarak isaretler.
     *
     * @param  array $uuids  UUID listesi
     */
    public function markTaken(array $uuids): array
    {
        return $this->http->action('SetInvoicesTaken', [
            'uuids' => $uuids,
        ]);
    }
}
