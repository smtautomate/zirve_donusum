<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ZirveDonusum\Services\DashboardService dashboard()
 * @method static \ZirveDonusum\Services\UserService user()
 * @method static \ZirveDonusum\Services\ContractService contracts()
 * @method static \ZirveDonusum\Services\InvoiceService invoices()
 * @method static \ZirveDonusum\Services\CompanyService company()
 * @method static \ZirveDonusum\Services\DespatchService despatch()
 * @method static \ZirveDonusum\Services\ReportService reports()
 * @method static \ZirveDonusum\HttpClient http()
 * @method static string|null getAccountId()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\ZirveDonusumClient
 */
class ZirveDonusum extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zirve-donusum';
    }
}
