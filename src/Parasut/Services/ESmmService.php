<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * E-SMM (Serbest Meslek Makbuzu) - /e_smms
 */
class ESmmService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('e_smms', $query);
    }

    public function show(int|string $id, array $query = []): array
    {
        return $this->http->get("e_smms/{$id}", $query);
    }

    public function send(int|string $salesInvoiceId, array $data): array
    {
        return $this->http->post("sales_invoices/{$salesInvoiceId}/e_smms", $data);
    }
}
