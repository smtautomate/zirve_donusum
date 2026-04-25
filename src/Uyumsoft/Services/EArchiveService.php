<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Arsiv servisi.
 * Endpoint: /Integration/EArsiv
 */
class EArchiveService extends BaseService
{
    public function send(array $invoice): array
    {
        return $this->http->post('Integration/EArsiv/SendInvoice', [
            'Invoice' => $invoice,
        ]);
    }

    public function sendBatch(array $invoices): array
    {
        return $this->http->post('Integration/EArsiv/SendInvoices', [
            'Invoices' => $invoices,
        ]);
    }

    public function list(array $filters = []): array
    {
        return $this->http->post('Integration/EArsiv/GetInvoices', $filters);
    }

    public function status(string $uuid): array
    {
        return $this->http->post('Integration/EArsiv/GetInvoiceStatus', [
            'Uuid' => $uuid,
        ]);
    }

    public function cancel(string $uuid, string $reason = ''): array
    {
        return $this->http->post('Integration/EArsiv/CancelInvoice', [
            'Uuid' => $uuid,
            'Reason' => $reason,
        ]);
    }

    public function download(string $uuid, string $format = 'pdf'): string
    {
        return $this->http->download("Integration/EArsiv/Download/{$uuid}", [
            'format' => $format,
        ]);
    }
}
