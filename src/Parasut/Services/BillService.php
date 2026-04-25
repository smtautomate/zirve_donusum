<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Satis Faturasi (Bill) - /sales_invoices
 * Standart CRUD + cancel, recover, archive, unArchive, pay, pdf, eInvoices, eArchives.
 */
class BillService extends BaseResourceService
{
    protected string $endpoint = 'sales_invoices';

    /**
     * Faturayi iptal eder (delete with cancel semantics).
     */
    public function cancel(int|string $id): array
    {
        return $this->http->delete("{$this->endpoint}/{$id}/cancel");
    }

    /**
     * Iptal edilmis faturayi geri yukler.
     */
    public function recover(int|string $id): array
    {
        return $this->http->patch("{$this->endpoint}/{$id}/recover");
    }

    /**
     * Faturayi arsivler.
     */
    public function archive(int|string $id): array
    {
        return $this->http->patch("{$this->endpoint}/{$id}/archive");
    }

    /**
     * Faturayi arsivden cikartir.
     */
    public function unArchive(int|string $id): array
    {
        return $this->http->patch("{$this->endpoint}/{$id}/unarchive");
    }

    /**
     * Faturaya odeme/tahsilat ekler.
     */
    public function pay(int|string $id, array $data): array
    {
        return $this->http->post("{$this->endpoint}/{$id}/payments", $data);
    }

    /**
     * Fatura PDF'ini indirir (binary).
     */
    public function pdf(int|string $id): string
    {
        return $this->http->download("{$this->endpoint}/{$id}/pdf");
    }

    /**
     * Faturayi e-Fatura'ya cevirir.
     */
    public function toEInvoice(int|string $id, array $data): array
    {
        return $this->http->post("{$this->endpoint}/{$id}/e_invoices", $data);
    }

    /**
     * Faturayi e-Arsiv'e cevirir.
     */
    public function toEArchive(int|string $id, array $data): array
    {
        return $this->http->post("{$this->endpoint}/{$id}/e_archives", $data);
    }
}
