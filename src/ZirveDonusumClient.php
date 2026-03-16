<?php

namespace ZirveDonusum;

use ZirveDonusum\Services\InvoiceService;
use ZirveDonusum\Services\CompanyService;
use ZirveDonusum\Services\DespatchService;
use ZirveDonusum\Services\ReportService;
use ZirveDonusum\Services\DashboardService;
use ZirveDonusum\Services\UserService;
use ZirveDonusum\Services\ContractService;
use ZirveDonusum\Services\LookupService;

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
 *   // Otomatik login + accountId resolve
 *   $services = $client->dashboard()->getUserServices();
 *   $faturalar = $client->invoices()->listIncoming();
 */
class ZirveDonusumClient
{
    private HttpClient $http;

    private ?DashboardService $dashboardService = null;
    private ?UserService $userService = null;
    private ?ContractService $contractService = null;
    private ?InvoiceService $invoiceService = null;
    private ?CompanyService $companyService = null;
    private ?DespatchService $despatchService = null;
    private ?ReportService $reportService = null;
    private ?LookupService $lookupService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    // ─── Service Accessors ───────────────────────────────────────────

    /** Dashboard / Ana sayfa verileri */
    public function dashboard(): DashboardService
    {
        return $this->dashboardService ??= new DashboardService($this->http);
    }

    /** Kullanıcı / Yetki bilgileri */
    public function user(): UserService
    {
        return $this->userService ??= new UserService($this->http);
    }

    /** Sözleşme işlemleri */
    public function contracts(): ContractService
    {
        return $this->contractService ??= new ContractService($this->http);
    }

    /** E-Fatura / E-Arşiv */
    public function invoices(): InvoiceService
    {
        return $this->invoiceService ??= new InvoiceService($this->http);
    }

    /** Firma / Mükellef */
    public function company(): CompanyService
    {
        return $this->companyService ??= new CompanyService($this->http);
    }

    /** E-İrsaliye */
    public function despatch(): DespatchService
    {
        return $this->despatchService ??= new DespatchService($this->http);
    }

    /** Raporlar */
    public function reports(): ReportService
    {
        return $this->reportService ??= new ReportService($this->http);
    }

    /** Referans verileri (il, vergi dairesi vb.) */
    public function lookup(): LookupService
    {
        return $this->lookupService ??= new LookupService($this->http);
    }

    // ─── Direct Access ───────────────────────────────────────────────

    /** Doğrudan HTTP client (custom endpoint'ler için) */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /** AccountId'yi al */
    public function getAccountId(): ?string
    {
        return $this->http->getAccountId();
    }

    /** AccountId'yi manuel set et */
    public function setAccountId(string $accountId): self
    {
        $this->http->setAccountId($accountId);
        return $this;
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
            return $this->http->getAccountId() !== null;
        } catch (\Throwable) {
            return false;
        }
    }
}
