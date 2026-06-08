<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft raporlama servisi.
 * API: POST /api/BasicIntegrationApi
 */
class ReportService extends BaseService
{
    /**
     * Fatura ozet raporu.
     *
     * @param  array $filters  orn. startDate, endDate, invoiceType
     */
    public function invoiceSummary(array $filters = []): array
    {
        return $this->http->action('InvoiceSummary', $filters);
    }

    /**
     * Aylik fatura raporu.
     */
    public function monthly(int $year, int $month): array
    {
        return $this->http->action('Monthly', [
            'year'  => $year,
            'month' => $month,
        ]);
    }
}
