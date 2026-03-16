<?php

namespace ZirveDonusum\Services;

/**
 * Raporlama
 */
class ReportService extends BaseService
{
    /**
     * Fatura özet raporu
     */
    public function invoiceSummary(array $filters = []): array
    {
        return $this->http->get($this->cp('report/GetInvoiceSummary'), $filters);
    }

    /**
     * Aylık rapor
     */
    public function monthly(string $year, string $month): array
    {
        return $this->http->get($this->cp('report/GetMonthlyReport'), [
            'year' => $year,
            'month' => $month,
        ]);
    }
}
