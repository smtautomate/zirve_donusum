<?php

namespace ZirveDonusum\Uyumsoft;

use ZirveDonusum\Uyumsoft\Services\CompanyService;
use ZirveDonusum\Uyumsoft\Services\EArchiveService;
use ZirveDonusum\Uyumsoft\Services\EInvoiceService;
use ZirveDonusum\Uyumsoft\Services\EWaybillService;
use ZirveDonusum\Uyumsoft\Services\ReportService;

/**
 * Uyumsoft E-Fatura / E-Arsiv / E-Irsaliye REST API Client.
 *
 * Stateless auth: her istekte UserInfo + Basic Auth header.
 * Test:  https://efatura-test.uyumsoft.com.tr
 * Prod:  https://efatura.uyumsoft.com.tr
 *
 * Kullanim:
 *   $client = new UyumsoftClient([
 *       'username' => '...', 'password' => '...',
 *       'test_mode' => true,
 *   ]);
 *   $client->eInvoice()->checkUser('1234567890');
 *   $client->eInvoice()->send($invoiceData);
 */
class UyumsoftClient
{
    private HttpClient $http;

    private ?EInvoiceService $eInvoiceService = null;
    private ?EArchiveService $eArchiveService = null;
    private ?EWaybillService $eWaybillService = null;
    private ?CompanyService $companyService = null;
    private ?ReportService $reportService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    /** E-Fatura */
    public function eInvoice(): EInvoiceService
    {
        return $this->eInvoiceService ??= new EInvoiceService($this->http);
    }

    /** E-Arsiv */
    public function eArchive(): EArchiveService
    {
        return $this->eArchiveService ??= new EArchiveService($this->http);
    }

    /** E-Irsaliye */
    public function eWaybill(): EWaybillService
    {
        return $this->eWaybillService ??= new EWaybillService($this->http);
    }

    /** Firma / Mukellef sorgulama */
    public function company(): CompanyService
    {
        return $this->companyService ??= new CompanyService($this->http);
    }

    /** Raporlama */
    public function reports(): ReportService
    {
        return $this->reportService ??= new ReportService($this->http);
    }

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function isTestMode(): bool
    {
        return $this->http->isTestMode();
    }

    public function testConnection(): bool
    {
        try {
            $info = $this->company()->info();
            return is_array($info);
        } catch (\Throwable) {
            return false;
        }
    }
}
