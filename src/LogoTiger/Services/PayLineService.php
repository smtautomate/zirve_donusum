<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Odeme Hareketleri (LG_XXX_PAYLINES) - tahsilat / tediye.
 * Endpoint: /payments
 */
class PayLineService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('payments', $query);
    }

    public function show(int|string $logicalRef): array
    {
        return $this->http->get("payments/{$logicalRef}");
    }

    public function create(array $data): array
    {
        return $this->http->post('payments', $data);
    }
}
