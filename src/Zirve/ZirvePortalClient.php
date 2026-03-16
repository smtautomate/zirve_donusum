<?php

namespace ZirveDonusum\Zirve;

use ZirveDonusum\Zirve\Services\AuthService;
use ZirveDonusum\Zirve\Services\UserService;
use ZirveDonusum\Zirve\Services\CommonService;
use ZirveDonusum\Zirve\Services\InvoiceService;
use ZirveDonusum\Zirve\Services\OutboxService;
use ZirveDonusum\Zirve\Services\InboxService;
use ZirveDonusum\Zirve\Services\EArchiveService;
use ZirveDonusum\Zirve\Services\DespatchService;
use ZirveDonusum\Zirve\Services\VoucherService;
use ZirveDonusum\Zirve\Services\CustomerService;
use ZirveDonusum\Zirve\Services\ExternalCustomerService;
use ZirveDonusum\Zirve\Services\StockService;
use ZirveDonusum\Zirve\Services\GibUserService;
use ZirveDonusum\Zirve\Services\CreditService;
use ZirveDonusum\Zirve\Services\ConnectorService;

/**
 * Zirve E-Dönüşüm Portal API Client
 *
 * JWT Bearer token ile çalışan yeni portal (yeniportal.zirvedonusum.com).
 * Mikro Portal'dan (eportal.mikrogrup.com) tamamen farklı bir API.
 *
 * Kullanım:
 *   $client = new ZirvePortalClient([
 *       'base_url' => 'https://yeniportal.zirvedonusum.com/accounting/api',
 *       'username' => '3599350000',
 *       'password' => 'sifre',
 *   ]);
 *
 *   $user = $client->users()->me();
 *   $faturalar = $client->outbox()->downloadHtml(['page' => 0, 'size' => 10]);
 */
class ZirvePortalClient
{
    private HttpClient $http;

    private ?AuthService $authService = null;
    private ?UserService $userService = null;
    private ?CommonService $commonService = null;
    private ?InvoiceService $invoiceService = null;
    private ?OutboxService $outboxService = null;
    private ?InboxService $inboxService = null;
    private ?EArchiveService $eArchiveService = null;
    private ?DespatchService $despatchService = null;
    private ?VoucherService $voucherService = null;
    private ?CustomerService $customerService = null;
    private ?ExternalCustomerService $externalCustomerService = null;
    private ?StockService $stockService = null;
    private ?GibUserService $gibUserService = null;
    private ?CreditService $creditService = null;
    private ?ConnectorService $connectorService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    // ─── Service Accessors ───────────────────────────────────────────

    /** Kimlik doğrulama */
    public function auth(): AuthService
    {
        return $this->authService ??= new AuthService($this->http);
    }

    /** Kullanıcı işlemleri */
    public function users(): UserService
    {
        return $this->userService ??= new UserService($this->http);
    }

    /** Ortak referans verileri */
    public function common(): CommonService
    {
        return $this->commonService ??= new CommonService($this->http);
    }

    /** Fatura taslak / gönderim */
    public function invoices(): InvoiceService
    {
        return $this->invoiceService ??= new InvoiceService($this->http);
    }

    /** Giden faturalar */
    public function outbox(): OutboxService
    {
        return $this->outboxService ??= new OutboxService($this->http);
    }

    /** Gelen faturalar */
    public function inbox(): InboxService
    {
        return $this->inboxService ??= new InboxService($this->http);
    }

    /** E-Arşiv */
    public function eArchive(): EArchiveService
    {
        return $this->eArchiveService ??= new EArchiveService($this->http);
    }

    /** E-İrsaliye */
    public function despatch(): DespatchService
    {
        return $this->despatchService ??= new DespatchService($this->http);
    }

    /** Serbest Meslek Makbuzu (E-SMM) */
    public function vouchers(): VoucherService
    {
        return $this->voucherService ??= new VoucherService($this->http);
    }

    /** Müşteri / Firma işlemleri */
    public function customers(): CustomerService
    {
        return $this->customerService ??= new CustomerService($this->http);
    }

    /** Harici müşteriler (cari hesaplar) */
    public function externalCustomers(): ExternalCustomerService
    {
        return $this->externalCustomerService ??= new ExternalCustomerService($this->http);
    }

    /** Stok / Ürün işlemleri */
    public function stocks(): StockService
    {
        return $this->stockService ??= new StockService($this->http);
    }

    /** GİB kullanıcı sorguları */
    public function gibUsers(): GibUserService
    {
        return $this->gibUserService ??= new GibUserService($this->http);
    }

    /** Kontör / Kredi işlemleri */
    public function credits(): CreditService
    {
        return $this->creditService ??= new CreditService($this->http);
    }

    /** ERP Connector işlemleri */
    public function connector(): ConnectorService
    {
        return $this->connectorService ??= new ConnectorService($this->http);
    }

    // ─── Direct Access ───────────────────────────────────────────────

    /** Doğrudan HTTP client (custom endpoint'ler için) */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /** parentCustomerId */
    public function getParentCustomerId(): ?int
    {
        return $this->http->getParentCustomerId();
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
            $me = $this->users()->me();
            return !empty($me['id']);
        } catch (\Throwable) {
            return false;
        }
    }
}
