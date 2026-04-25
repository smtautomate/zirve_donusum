<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Uyumsoft E-Fatura/E-Arsiv/E-Irsaliye client facade.
 *
 * @method static \ZirveDonusum\Uyumsoft\Services\EInvoiceService eInvoice()
 * @method static \ZirveDonusum\Uyumsoft\Services\EArchiveService eArchive()
 * @method static \ZirveDonusum\Uyumsoft\Services\EWaybillService eWaybill()
 * @method static \ZirveDonusum\Uyumsoft\Services\CompanyService company()
 * @method static \ZirveDonusum\Uyumsoft\Services\ReportService reports()
 * @method static bool isTestMode()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\Uyumsoft\UyumsoftClient
 */
class Uyumsoft extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'uyumsoft';
    }
}
