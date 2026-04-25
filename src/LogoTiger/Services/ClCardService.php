<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Cari Kartlari (LG_XXX_CLCARD) - musteri / saticilar.
 * Endpoint: /clCards
 */
class ClCardService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('clCards', $query);
    }

    public function show(int|string $logicalRef): array
    {
        return $this->http->get("clCards/{$logicalRef}");
    }

    public function create(array $data): array
    {
        return $this->http->post('clCards', $data);
    }

    public function update(int|string $logicalRef, array $data): array
    {
        return $this->http->put("clCards/{$logicalRef}", $data);
    }

    public function delete(int|string $logicalRef): array
    {
        return $this->http->delete("clCards/{$logicalRef}");
    }

    /**
     * VKN/TCKN'ye gore cari ara.
     */
    public function findByTaxNumber(string $taxNumber): array
    {
        return $this->http->get('clCards', ['taxNumber' => $taxNumber]);
    }
}
