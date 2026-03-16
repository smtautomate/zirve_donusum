<?php

namespace ZirveDonusum;

use ZirveDonusum\Services\InvoiceService;
use ZirveDonusum\Services\CompanyService;
use ZirveDonusum\Services\DespatchService;
use ZirveDonusum\Services\ReportService;

/**
 * eMikro (Zirve Dönüşüm) API Client
 *
 * Kullanım:
 *   $client = new ZirveDonusumClient([
 *       'base_url' => 'https://eportal.mikrogrup.com',
 *       'email'    => 'email@example.com',
 *       'password' => 'sifre',
 *   ]);
 *
 *   $faturalar = $client->invoices()->listIncoming();
 */
class ZirveDonusumClient
{
    private HttpClient $http;

    private ?InvoiceService $invoiceService = null;
    private ?CompanyService $companyService = null;
    private ?DespatchService $despatchService = null;
    private ?ReportService $reportService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    // ─── Service Accessors ───────────────────────────────────────────

    public function invoices(): InvoiceService
    {
        return $this->invoiceService ??= new InvoiceService($this->http);
    }

    public function company(): CompanyService
    {
        return $this->companyService ??= new CompanyService($this->http);
    }

    public function despatch(): DespatchService
    {
        return $this->despatchService ??= new DespatchService($this->http);
    }

    public function reports(): ReportService
    {
        return $this->reportService ??= new ReportService($this->http);
    }

    // ─── Direct HTTP Access ──────────────────────────────────────────

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function login(): bool
    {
        return $this->http->login();
    }

    public function logout(): void
    {
        $this->http->logout();
    }

    public function testConnection(): bool
    {
        try {
            $this->http->login();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
