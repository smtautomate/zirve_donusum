<?php

namespace ZirveDonusum\LogoTiger;

use ZirveDonusum\LogoTiger\Services\ClCardService;
use ZirveDonusum\LogoTiger\Services\EArchiveService;
use ZirveDonusum\LogoTiger\Services\EInvoiceService;
use ZirveDonusum\LogoTiger\Services\EWaybillService;
use ZirveDonusum\LogoTiger\Services\ItemService;
use ZirveDonusum\LogoTiger\Services\OrFicheService;
use ZirveDonusum\LogoTiger\Services\PayLineService;
use ZirveDonusum\LogoTiger\Services\StFicheService;

/**
 * Logo Tiger REST API Client (Logo Connect / Logo REST API).
 *
 * Auth: OAuth2 password grant + firmaNo + donemNo.
 * Tipik base URL: http://localhost:32001/api/v1/
 *
 * Kullanim:
 *   $client = new LogoTigerClient([
 *       'base_url' => 'http://erp.firma.local:32001/api/v1/',
 *       'username' => 'admin', 'password' => '...',
 *       'firma_no' => '1', 'donem_no' => '1',
 *   ]);
 *   $items = $client->items()->index();
 *   $client->eInvoice()->send($invoiceData);
 */
class LogoTigerClient
{
    private HttpClient $http;

    private ?ItemService $itemService = null;
    private ?ClCardService $clCardService = null;
    private ?OrFicheService $orFicheService = null;
    private ?StFicheService $stFicheService = null;
    private ?PayLineService $payLineService = null;
    private ?EInvoiceService $eInvoiceService = null;
    private ?EArchiveService $eArchiveService = null;
    private ?EWaybillService $eWaybillService = null;

    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    /** Stok kartlari (LG_ITEMS) */
    public function items(): ItemService
    {
        return $this->itemService ??= new ItemService($this->http);
    }

    /** Cari kartlari (LG_CLCARD) */
    public function clCards(): ClCardService
    {
        return $this->clCardService ??= new ClCardService($this->http);
    }

    /** Siparisler (LG_ORFICHE) */
    public function orders(): OrFicheService
    {
        return $this->orFicheService ??= new OrFicheService($this->http);
    }

    /** Faturalar (LG_STFICHE) */
    public function invoices(): StFicheService
    {
        return $this->stFicheService ??= new StFicheService($this->http);
    }

    /** Odeme hareketleri (LG_PAYLINES) */
    public function payments(): PayLineService
    {
        return $this->payLineService ??= new PayLineService($this->http);
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
            return $this->http->isAuthenticated();
        } catch (\Throwable) {
            return false;
        }
    }
}
