<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Mikro Portal (eportal.mikrogrup.com) client facade
 *
 * @method static \ZirveDonusum\Mikro\Services\DashboardService dashboard()
 * @method static \ZirveDonusum\Mikro\Services\UserService user()
 * @method static \ZirveDonusum\Mikro\Services\InvoiceService invoices()
 * @method static \ZirveDonusum\Mikro\Services\CompanyService company()
 * @method static \ZirveDonusum\Mikro\Services\DespatchService despatch()
 * @method static \ZirveDonusum\Mikro\Services\CustomerService customers()
 * @method static \ZirveDonusum\Mikro\Services\ReportService reports()
 * @method static \ZirveDonusum\Mikro\Services\ProductService products()
 * @method static \ZirveDonusum\Mikro\Services\LookupService lookup()
 * @method static \ZirveDonusum\Mikro\Services\ContractService contracts()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\Mikro\MikroClient
 */
class MikroPortal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mikro-portal';
    }
}
