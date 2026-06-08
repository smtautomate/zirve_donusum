<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Arsiv servisi.
 * API: POST /api/BasicIntegrationApi (E-Fatura ile aynı endpoint)
 * E-Arsiv fatürasini E-Fatura'dan ayiran alan: invoices[].EArchiveInvoiceInfo.DeliveryType
 * Referans: iuyum_api/Bilgi Sistemleri/E-Fatura&E-Arsiv/RestAPI/Request/
 */
class EArchiveService extends BaseService
{
    /**
     * E-Arsiv fatura gonder.
     *
     * Her eleman:
     * [
     *   "Invoice" => {...UBL-TR...},
     *   "EArchiveInvoiceInfo" => ["DeliveryType" => "Electronic"],
     *   "Scenario" => 0,
     *   "Notification" => [...],
     *   "LocalDocumentId" => "...",
     * ]
     *
     * @param  array $invoices
     */
    public function send(array $invoices): array
    {
        return $this->http->action('SendInvoice', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * E-Arsiv gelen fatura listesi.
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->action('GetInboxInvoiceList', $filters);
    }

    /**
     * E-Arsiv giden fatura listesi.
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->action('GetOutboxInvoiceList', $filters);
    }

    /**
     * Gelen e-Arsiv fatura detayi.
     */
    public function getIncoming(string $uuid): array
    {
        return $this->http->action('GetInboxInvoiceView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Giden e-Arsiv fatura detayi.
     */
    public function getOutgoing(string $uuid): array
    {
        return $this->http->action('GetOutboxInvoiceView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * E-Arsiv fatura PDF goruntule.
     */
    public function getPdf(string $uuid): array
    {
        return $this->http->action('GetPdfViewRequest', [
            'uuid' => $uuid,
            'type' => 1,
        ]);
    }

    /**
     * E-Arsiv fatura iptal et.
     */
    public function cancelDraft(string $uuid): array
    {
        return $this->http->action('CancelDraft', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * E-Arsiv taslak yayimla.
     */
    public function sendDraft(string $uuid): array
    {
        return $this->http->action('SendDraft', [
            'uuid' => $uuid,
        ]);
    }
}
