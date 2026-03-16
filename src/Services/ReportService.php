<?php

namespace ZirveDonusum\Services;

/**
 * Raporlama / GİB Rapor Gönderme
 *
 * Gerçek endpoint'ler:
 *   GET /cp/{accountId}/GibReport/GetDocumentList
 *   Sayfa: /cp/{accountId}/gibReport/Index?documentType=EArchive
 */
class ReportService extends BaseService
{
    /**
     * GİB Rapor belge listesi (Rapor Gönderme Talebi)
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/GibReport/GetDocumentList
     */
    public function getGibReportDocumentList(): array
    {
        return $this->http->get($this->cp('GibReport/GetDocumentList'));
    }

    /**
     * Yükleme / Transfer belgeleri listesi
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/TransferDocuments/GetTransferDocumentList
     */
    public function getTransferDocumentList(): array
    {
        return $this->http->get($this->cp('TransferDocuments/GetTransferDocumentList'));
    }

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
