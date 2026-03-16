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
        return $this->http->get('/report/getInvoiceSummary', $filters);
    }

    /**
     * Aylık rapor
     */
    public function monthly(string $year, string $month): array
    {
        return $this->http->get('/report/getMonthlyReport', [
            'year' => $year,
            'month' => $month,
        ]);
    }
}
