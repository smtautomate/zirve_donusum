<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * E-Fatura - /e_invoices
 * Sadece show + index + create (sales_invoice'tan donusum BillService::toEInvoice ile).
 */
class EBillService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('e_invoices', $query);
    }

    public function show(int|string $id, array $query = []): array
    {
        return $this->http->get("e_invoices/{$id}", $query);
    }

    /**
     * sales_invoices/{id}/e_invoices ile gonderim (BillService::toEInvoice icin shortcut).
     */
    public function send(int|string $salesInvoiceId, array $data): array
    {
        return $this->http->post("sales_invoices/{$salesInvoiceId}/e_invoices", $data);
    }
}
