<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Fatura servisi.
 * Endpoint: /Integration/EFatura
 */
class EInvoiceService extends BaseService
{
    /**
     * Mukellefin e-Fatura kullanici olup olmadigini kontrol eder.
     */
    public function checkUser(string $taxNumber): array
    {
        return $this->http->post('Integration/EFatura/CheckUser', [
            'VknTckn' => $taxNumber,
        ]);
    }

    /**
     * E-Fatura gonder (UBL-TR XML / JSON payload).
     */
    public function send(array $invoice): array
    {
        return $this->http->post('Integration/EFatura/SendInvoice', [
            'Invoice' => $invoice,
        ]);
    }

    /**
     * Toplu e-Fatura gonder.
     */
    public function sendBatch(array $invoices): array
    {
        return $this->http->post('Integration/EFatura/SendInvoices', [
            'Invoices' => $invoices,
        ]);
    }

    /**
     * Giden e-Fatura listesi.
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->post('Integration/EFatura/GetOutboxInvoices', $filters);
    }

    /**
     * Gelen e-Fatura listesi.
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->post('Integration/EFatura/GetInboxInvoices', $filters);
    }

    /**
     * Fatura durum sorgulama (UUID/ETTN ile).
     */
    public function status(string $uuid): array
    {
        return $this->http->post('Integration/EFatura/GetInvoiceStatus', [
            'Uuid' => $uuid,
        ]);
    }

    /**
     * Faturayi kabul et.
     */
    public function accept(string $uuid): array
    {
        return $this->http->post('Integration/EFatura/AcceptInvoice', [
            'Uuid' => $uuid,
        ]);
    }

    /**
     * Faturayi reddet.
     */
    public function reject(string $uuid, string $reason): array
    {
        return $this->http->post('Integration/EFatura/RejectInvoice', [
            'Uuid' => $uuid,
            'Reason' => $reason,
        ]);
    }

    /**
     * Faturayi iptal et.
     */
    public function cancel(string $uuid, string $reason = ''): array
    {
        return $this->http->post('Integration/EFatura/CancelInvoice', [
            'Uuid' => $uuid,
            'Reason' => $reason,
        ]);
    }

    /**
     * Fatura PDF/XML indir.
     */
    public function download(string $uuid, string $format = 'pdf'): string
    {
        return $this->http->download("Integration/EFatura/Download/{$uuid}", [
            'format' => $format,
        ]);
    }
}
