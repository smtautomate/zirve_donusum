<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * GİB e-Arşiv Portal client facade.
 *
 * Laravel uygulamalarında GİB e-Arşiv portalına kolay erişim sağlar.
 *
 * @method static \ZirveDonusum\Gib\Services\AuthService auth()
 * @method static \ZirveDonusum\Gib\Services\InvoiceService invoices()
 * @method static \ZirveDonusum\Gib\Services\UserService users()
 * @method static \ZirveDonusum\Gib\HttpClient http()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 * @method static bool isTestMode()
 *
 * @see \ZirveDonusum\Gib\GibClient
 */
class GibPortal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gib-portal';
    }
}
