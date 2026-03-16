<?php

namespace ZirveDonusum\Mikro;

use ZirveDonusum\Mikro\Services\InvoiceService;
use ZirveDonusum\Mikro\Services\CompanyService;
use ZirveDonusum\Mikro\Services\DespatchService;
use ZirveDonusum\Mikro\Services\ReportService;
use ZirveDonusum\Mikro\Services\DashboardService;
use ZirveDonusum\Mikro\Services\UserService;
use ZirveDonusum\Mikro\Services\ContractService;
use ZirveDonusum\Mikro\Services\LookupService;
use ZirveDonusum\Mikro\Services\CustomerService;
use ZirveDonusum\Mikro\Services\ProductService;

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
class MikroClient
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
    private ?CustomerService $customerService = null;
    private ?ProductService $productService = null;

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

    /** Müşteri işlemleri */
    public function customers(): CustomerService
    {
        return $this->customerService ??= new CustomerService($this->http);
    }

    /** Ürün / Hizmet işlemleri */
    public function products(): ProductService
    {
        return $this->productService ??= new ProductService($this->http);
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
