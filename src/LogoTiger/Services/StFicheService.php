<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Fatura Fisleri (LG_XXX_STFICHE) - alis / satis faturalari.
 * Endpoint: /salesInvoices, /purchaseInvoices
 */
class StFicheService extends BaseService
{
    public function listSalesInvoices(array $query = []): array
    {
        return $this->http->get('salesInvoices', $query);
    }

    public function listPurchaseInvoices(array $query = []): array
    {
        return $this->http->get('purchaseInvoices', $query);
    }

    public function showSalesInvoice(int|string $logicalRef): array
    {
        return $this->http->get("salesInvoices/{$logicalRef}");
    }

    public function showPurchaseInvoice(int|string $logicalRef): array
    {
        return $this->http->get("purchaseInvoices/{$logicalRef}");
    }

    public function createSalesInvoice(array $data): array
    {
        return $this->http->post('salesInvoices', $data);
    }

    public function createPurchaseInvoice(array $data): array
    {
        return $this->http->post('purchaseInvoices', $data);
    }

    public function deleteSalesInvoice(int|string $logicalRef): array
    {
        return $this->http->delete("salesInvoices/{$logicalRef}");
    }
}
