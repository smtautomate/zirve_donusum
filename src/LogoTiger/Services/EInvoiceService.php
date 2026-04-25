<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Logo Tiger E-Fatura entegrasyonu.
 * Endpoint: /eInvoices
 */
class EInvoiceService extends BaseService
{
    public function listOutgoing(array $query = []): array
    {
        return $this->http->get('eInvoices/outgoing', $query);
    }

    public function listIncoming(array $query = []): array
    {
        return $this->http->get('eInvoices/incoming', $query);
    }

    public function show(int|string $id): array
    {
        return $this->http->get("eInvoices/{$id}");
    }

    public function send(array $data): array
    {
        return $this->http->post('eInvoices/send', $data);
    }

    public function status(int|string $id): array
    {
        return $this->http->get("eInvoices/{$id}/status");
    }

    public function cancel(int|string $id, array $data = []): array
    {
        return $this->http->post("eInvoices/{$id}/cancel", $data);
    }

    public function downloadPdf(int|string $id): string
    {
        return $this->http->download("eInvoices/{$id}/pdf");
    }

    public function downloadXml(int|string $id): string
    {
        return $this->http->download("eInvoices/{$id}/xml");
    }
}
