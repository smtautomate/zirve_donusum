<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Standart Parasut resource (JSON:API tarzi /sales_invoices, /contacts vb.)
 * 5 standart metod sunar: index, create, show, edit, delete.
 *
 * Endpoint child sinifta override edilir veya constructor ile gecirilir.
 */
abstract class BaseResourceService extends BaseService
{
    /**
     * Sirket-scope endpoint adi. Ornegin: 'sales_invoices'
     */
    protected string $endpoint = '';

    public function index(array $query = []): array
    {
        return $this->http->get($this->endpoint, $query);
    }

    public function create(array $data): array
    {
        return $this->http->post($this->endpoint, $data);
    }

    public function show(int|string $id, array $query = []): array
    {
        return $this->http->get("{$this->endpoint}/{$id}", $query);
    }

    public function edit(int|string $id, array $data): array
    {
        return $this->http->put("{$this->endpoint}/{$id}", $data);
    }

    public function delete(int|string $id): array
    {
        return $this->http->delete("{$this->endpoint}/{$id}");
    }
}
