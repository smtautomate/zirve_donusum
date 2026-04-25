<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * E-Arsiv - /e_archives
 */
class EArchiveService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('e_archives', $query);
    }

    public function show(int|string $id, array $query = []): array
    {
        return $this->http->get("e_archives/{$id}", $query);
    }

    public function send(int|string $salesInvoiceId, array $data): array
    {
        return $this->http->post("sales_invoices/{$salesInvoiceId}/e_archives", $data);
    }
}
