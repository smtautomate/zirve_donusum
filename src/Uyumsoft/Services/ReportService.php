<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft raporlama servisi.
 */
class ReportService extends BaseService
{
    public function invoiceSummary(array $filters = []): array
    {
        return $this->http->post('Integration/Reports/InvoiceSummary', $filters);
    }

    public function monthly(int $year, int $month): array
    {
        return $this->http->post('Integration/Reports/Monthly', [
            'Year' => $year,
            'Month' => $month,
        ]);
    }
}
