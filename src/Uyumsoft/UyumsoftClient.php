<?php

namespace ZirveDonusum\Uyumsoft;

use ZirveDonusum\Uyumsoft\Services\CompanyService;
use ZirveDonusum\Uyumsoft\Services\EAdisyonService;
use ZirveDonusum\Uyumsoft\Services\EArchiveService;
use ZirveDonusum\Uyumsoft\Services\EBiletService;
use ZirveDonusum\Uyumsoft\Services\EInvoiceService;
use ZirveDonusum\Uyumsoft\Services\EWaybillService;
use ZirveDonusum\Uyumsoft\Services\ReportService;

/**
 * Uyumsoft API ana client.
 *
 * API endpoint'leri:
 *   E-Fatura / E-Arsiv: POST /api/BasicIntegrationApi
 *   E-Irsaliye:         POST /api/DespatchApi
 *   E-Adisyon:          POST /api/AdisyonApi
 *   E-Bilet:            POST /api/BiletApi
 *
 * Kullanim:
 *   $client = new UyumsoftClient([
 *       'username'  => '...',
 *       'password'  => '...',
 *       'test_mode' => true,
 *   ]);
 *   $client->eInvoice()->isUser('1234567890');
 *   $client->eWaybill()->send($despatches);
 *   $client->eAdisyon()->send($guestChecks);
 */
class UyumsoftClient
{
    private HttpClient $invoiceHttp;
    private HttpClient $despatchHttp;
    private HttpClient $adisyonHttp;
    private HttpClient $biletHttp;

    private ?EInvoiceService $eInvoiceService   = null;
    private ?EArchiveService $eArchiveService   = null;
    private ?EWaybillService $eWaybillService   = null;
    private ?EAdisyonService $eAdisyonService   = null;
    private ?EBiletService   $eBiletService     = null;
    private ?CompanyService  $companyService    = null;
    private ?ReportService   $reportService     = null;

    public function __construct(array $config)
    {
        $this->invoiceHttp = new HttpClient($config, 'BasicIntegrationApi');
        $this->despatchHttp = new HttpClient($config, 'DespatchApi');
        $this->adisyonHttp  = new HttpClient($config, $config['adisyon_api_path'] ?? 'AdisyonApi');
        $this->biletHttp    = new HttpClient($config, $config['bilet_api_path'] ?? 'BiletApi');
    }

    /** E-Fatura */
    public function eInvoice(): EInvoiceService
    {
        return $this->eInvoiceService ??= new EInvoiceService($this->invoiceHttp);
    }

    /** E-Arsiv */
    public function eArchive(): EArchiveService
    {
        return $this->eArchiveService ??= new EArchiveService($this->invoiceHttp);
    }

    /** E-Irsaliye */
    public function eWaybill(): EWaybillService
    {
        return $this->eWaybillService ??= new EWaybillService($this->despatchHttp);
    }

    /** E-Adisyon (Restoran/Kafe adisyon belgesi) */
    public function eAdisyon(): EAdisyonService
    {
        return $this->eAdisyonService ??= new EAdisyonService($this->adisyonHttp);
    }

    /** E-Bilet */
    public function eBilet(): EBiletService
    {
        return $this->eBiletService ??= new EBiletService($this->biletHttp);
    }

    /** Mukellef / firma sorgulama */
    public function company(): CompanyService
    {
        return $this->companyService ??= new CompanyService($this->invoiceHttp);
    }

    /** Raporlama */
    public function reports(): ReportService
    {
        return $this->reportService ??= new ReportService($this->invoiceHttp);
    }

    public function isTestMode(): bool
    {
        return $this->invoiceHttp->isTestMode();
    }

    /**
     * Baglanti testi: e-Fatura kullanici sorgusu ile kimlik dogrulama kontrol eder.
     */
    public function testConnection(): bool
    {
        try {
            $this->eInvoice()->getUsers();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
