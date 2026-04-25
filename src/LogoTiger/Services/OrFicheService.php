<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Siparis Fisleri (LG_XXX_ORFICHE) - alis / satis siparisleri.
 * Endpoint: /salesOrders, /purchaseOrders
 */
class OrFicheService extends BaseService
{
    public function listSalesOrders(array $query = []): array
    {
        return $this->http->get('salesOrders', $query);
    }

    public function listPurchaseOrders(array $query = []): array
    {
        return $this->http->get('purchaseOrders', $query);
    }

    public function showSalesOrder(int|string $logicalRef): array
    {
        return $this->http->get("salesOrders/{$logicalRef}");
    }

    public function showPurchaseOrder(int|string $logicalRef): array
    {
        return $this->http->get("purchaseOrders/{$logicalRef}");
    }

    public function createSalesOrder(array $data): array
    {
        return $this->http->post('salesOrders', $data);
    }

    public function createPurchaseOrder(array $data): array
    {
        return $this->http->post('purchaseOrders', $data);
    }
}
