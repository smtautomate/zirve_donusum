<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Zirve Portal (yeniportal.zirvedonusum.com) client facade
 *
 * @method static \ZirveDonusum\Zirve\Services\AuthService auth()
 * @method static \ZirveDonusum\Zirve\Services\UserService users()
 * @method static \ZirveDonusum\Zirve\Services\CommonService common()
 * @method static \ZirveDonusum\Zirve\Services\InvoiceService invoices()
 * @method static \ZirveDonusum\Zirve\Services\OutboxService outbox()
 * @method static \ZirveDonusum\Zirve\Services\InboxService inbox()
 * @method static \ZirveDonusum\Zirve\Services\EArchiveService eArchive()
 * @method static \ZirveDonusum\Zirve\Services\DespatchService despatch()
 * @method static \ZirveDonusum\Zirve\Services\VoucherService vouchers()
 * @method static \ZirveDonusum\Zirve\Services\CustomerService customers()
 * @method static \ZirveDonusum\Zirve\Services\ExternalCustomerService externalCustomers()
 * @method static \ZirveDonusum\Zirve\Services\StockService stocks()
 * @method static \ZirveDonusum\Zirve\Services\GibUserService gibUsers()
 * @method static \ZirveDonusum\Zirve\Services\CreditService credits()
 * @method static \ZirveDonusum\Zirve\Services\ConnectorService connector()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\Zirve\ZirvePortalClient
 */
class ZirvePortal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zirve-portal';
    }
}
