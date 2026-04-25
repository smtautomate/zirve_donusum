<?php

namespace ZirveDonusum\Parasut;

use ZirveDonusum\Parasut\Services\AccountService;
use ZirveDonusum\Parasut\Services\ApiHomeService;
use ZirveDonusum\Parasut\Services\BankService;
use ZirveDonusum\Parasut\Services\BillService;
use ZirveDonusum\Parasut\Services\CategoryService;
use ZirveDonusum\Parasut\Services\CustomerService;
use ZirveDonusum\Parasut\Services\EArchiveService;
use ZirveDonusum\Parasut\Services\EBillService;
use ZirveDonusum\Parasut\Services\EmployeeService;
use ZirveDonusum\Parasut\Services\ESmmService;
use ZirveDonusum\Parasut\Services\InboxService;
use ZirveDonusum\Parasut\Services\ProductService;
use ZirveDonusum\Parasut\Services\ReceiptService;
use ZirveDonusum\Parasut\Services\SalaryService;
use ZirveDonusum\Parasut\Services\StockMovementService;
use ZirveDonusum\Parasut\Services\SupplierService;
use ZirveDonusum\Parasut\Services\TagService;
use ZirveDonusum\Parasut\Services\TaxService;
use ZirveDonusum\Parasut\Services\TrackableJobService;
use ZirveDonusum\Parasut\Services\TransactionService;
use ZirveDonusum\Parasut\Services\WarehouseService;
use ZirveDonusum\Parasut\Services\WaybillService;
use ZirveDonusum\Parasut\Services\WebhookService;

/**
 * Parasut V4 API Client.
 *
 * OAuth2 password grant ile auth, /v4/{companyId}/{resource} endpoint yapisi.
 *
 * Kullanim:
 *   $client = new ParasutClient([
 *       'username' => '...', 'password' => '...',
 *       'company_id' => '12345',
 *       'client_id' => '...', 'client_secret' => '...',
 *   ]);
 *   $bills = $client->bills()->index();
 *   $client->eBills()->send($salesInvoiceId, $payload);
 *
 * Kapsanan 23 resource: theposeidonas/laravel-parasut-api ile birebir.
 */
class ParasutClient
{
    private HttpClient $http;

    private ?BillService $billService = null;
    private ?CustomerService $customerService = null;
    private ?SupplierService $supplierService = null;
    private ?ProductService $productService = null;
    private ?EBillService $eBillService = null;
    private ?EArchiveService $eArchiveService = null;
    private ?ESmmService $eSmmService = null;
    private ?InboxService $inboxService = null;
    private ?ReceiptService $receiptService = null;
    private ?BankService $bankService = null;
    private ?SalaryService $salaryService = null;
    private ?TaxService $taxService = null;
    private ?EmployeeService $employeeService = null;
    private ?AccountService $accountService = null;
    private ?TransactionService $transactionService = null;
    private ?WarehouseService $warehouseService = null;
    private ?WaybillService $waybillService = null;
    private ?StockMovementService $stockMovementService = null;
    private ?CategoryService $categoryService = null;
    private ?TagService $tagService = null;
    private ?ApiHomeService $apiHomeService = null;
    private ?TrackableJobService $trackableJobService = null;
    private ?WebhookService $webhookService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    // ─── Sales / E-Donusum ────────────────────────────────────────────

    /** Satis faturasi (sales_invoices) */
    public function bills(): BillService
    {
        return $this->billService ??= new BillService($this->http);
    }

    /** E-Fatura (e_invoices) */
    public function eBills(): EBillService
    {
        return $this->eBillService ??= new EBillService($this->http);
    }

    /** E-Arsiv (e_archives) */
    public function eArchives(): EArchiveService
    {
        return $this->eArchiveService ??= new EArchiveService($this->http);
    }

    /** Serbest Meslek Makbuzu (e_smms) */
    public function eSmms(): ESmmService
    {
        return $this->eSmmService ??= new ESmmService($this->http);
    }

    /** Gelen e-fatura kutusu (e_invoice_inboxes) */
    public function inbox(): InboxService
    {
        return $this->inboxService ??= new InboxService($this->http);
    }

    // ─── Contacts ────────────────────────────────────────────────────

    /** Musteriler (contacts, account_type=customer) */
    public function customers(): CustomerService
    {
        return $this->customerService ??= new CustomerService($this->http);
    }

    /** Tedarikciler (contacts, account_type=supplier) */
    public function suppliers(): SupplierService
    {
        return $this->supplierService ??= new SupplierService($this->http);
    }

    /** Calisanlar */
    public function employees(): EmployeeService
    {
        return $this->employeeService ??= new EmployeeService($this->http);
    }

    // ─── Inventory ───────────────────────────────────────────────────

    /** Urunler */
    public function products(): ProductService
    {
        return $this->productService ??= new ProductService($this->http);
    }

    /** Depolar */
    public function warehouses(): WarehouseService
    {
        return $this->warehouseService ??= new WarehouseService($this->http);
    }

    /** Irsaliyeler */
    public function waybills(): WaybillService
    {
        return $this->waybillService ??= new WaybillService($this->http);
    }

    /** Stok hareketleri */
    public function stockMovements(): StockMovementService
    {
        return $this->stockMovementService ??= new StockMovementService($this->http);
    }

    // ─── Cash / Expenses ─────────────────────────────────────────────

    /** Hesaplar (kasa/banka) */
    public function accounts(): AccountService
    {
        return $this->accountService ??= new AccountService($this->http);
    }

    /** Islemler (transactions) */
    public function transactions(): TransactionService
    {
        return $this->transactionService ??= new TransactionService($this->http);
    }

    /** Tahsilat / Tediye */
    public function receipts(): ReceiptService
    {
        return $this->receiptService ??= new ReceiptService($this->http);
    }

    /** Banka masraflari */
    public function banks(): BankService
    {
        return $this->bankService ??= new BankService($this->http);
    }

    /** Maaslar */
    public function salaries(): SalaryService
    {
        return $this->salaryService ??= new SalaryService($this->http);
    }

    /** Vergiler */
    public function taxes(): TaxService
    {
        return $this->taxService ??= new TaxService($this->http);
    }

    // ─── Settings / Meta ─────────────────────────────────────────────

    /** Kategoriler */
    public function categories(): CategoryService
    {
        return $this->categoryService ??= new CategoryService($this->http);
    }

    /** Etiketler */
    public function tags(): TagService
    {
        return $this->tagService ??= new TagService($this->http);
    }

    /** API Home (root + me) */
    public function apiHome(): ApiHomeService
    {
        return $this->apiHomeService ??= new ApiHomeService($this->http);
    }

    /** Asenkron is takibi */
    public function trackableJobs(): TrackableJobService
    {
        return $this->trackableJobService ??= new TrackableJobService($this->http);
    }

    /** Webhook'lar */
    public function webhooks(): WebhookService
    {
        return $this->webhookService ??= new WebhookService($this->http);
    }

    // ─── Direct Access ───────────────────────────────────────────────

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function getCompanyId(): string
    {
        return $this->http->getCompanyId();
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
            $me = $this->apiHome()->me();
            return !empty($me);
        } catch (\Throwable) {
            return false;
        }
    }
}
