<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Stok Kartlari (LG_XXX_ITEMS) - urun/malzeme kartlari.
 * Endpoint: /items
 */
class ItemService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('items', $query);
    }

    public function show(int|string $logicalRef): array
    {
        return $this->http->get("items/{$logicalRef}");
    }

    public function create(array $data): array
    {
        return $this->http->post('items', $data);
    }

    public function update(int|string $logicalRef, array $data): array
    {
        return $this->http->put("items/{$logicalRef}", $data);
    }

    public function delete(int|string $logicalRef): array
    {
        return $this->http->delete("items/{$logicalRef}");
    }
}
