<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Logo Tiger REST API client facade.
 *
 * @method static \ZirveDonusum\LogoTiger\Services\ItemService items()
 * @method static \ZirveDonusum\LogoTiger\Services\ClCardService clCards()
 * @method static \ZirveDonusum\LogoTiger\Services\OrFicheService orders()
 * @method static \ZirveDonusum\LogoTiger\Services\StFicheService invoices()
 * @method static \ZirveDonusum\LogoTiger\Services\PayLineService payments()
 * @method static \ZirveDonusum\LogoTiger\Services\EInvoiceService eInvoice()
 * @method static \ZirveDonusum\LogoTiger\Services\EArchiveService eArchive()
 * @method static \ZirveDonusum\LogoTiger\Services\EWaybillService eWaybill()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\LogoTiger\LogoTigerClient
 */
class LogoTiger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'logo-tiger';
    }
}
