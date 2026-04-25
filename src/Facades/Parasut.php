<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Parasut V4 API client facade.
 *
 * @method static \ZirveDonusum\Parasut\Services\BillService bills()
 * @method static \ZirveDonusum\Parasut\Services\EBillService eBills()
 * @method static \ZirveDonusum\Parasut\Services\EArchiveService eArchives()
 * @method static \ZirveDonusum\Parasut\Services\ESmmService eSmms()
 * @method static \ZirveDonusum\Parasut\Services\InboxService inbox()
 * @method static \ZirveDonusum\Parasut\Services\CustomerService customers()
 * @method static \ZirveDonusum\Parasut\Services\SupplierService suppliers()
 * @method static \ZirveDonusum\Parasut\Services\EmployeeService employees()
 * @method static \ZirveDonusum\Parasut\Services\ProductService products()
 * @method static \ZirveDonusum\Parasut\Services\WarehouseService warehouses()
 * @method static \ZirveDonusum\Parasut\Services\WaybillService waybills()
 * @method static \ZirveDonusum\Parasut\Services\StockMovementService stockMovements()
 * @method static \ZirveDonusum\Parasut\Services\AccountService accounts()
 * @method static \ZirveDonusum\Parasut\Services\TransactionService transactions()
 * @method static \ZirveDonusum\Parasut\Services\ReceiptService receipts()
 * @method static \ZirveDonusum\Parasut\Services\BankService banks()
 * @method static \ZirveDonusum\Parasut\Services\SalaryService salaries()
 * @method static \ZirveDonusum\Parasut\Services\TaxService taxes()
 * @method static \ZirveDonusum\Parasut\Services\CategoryService categories()
 * @method static \ZirveDonusum\Parasut\Services\TagService tags()
 * @method static \ZirveDonusum\Parasut\Services\ApiHomeService apiHome()
 * @method static \ZirveDonusum\Parasut\Services\TrackableJobService trackableJobs()
 * @method static \ZirveDonusum\Parasut\Services\WebhookService webhooks()
 * @method static bool login()
 * @method static void logout()
 * @method static bool testConnection()
 *
 * @see \ZirveDonusum\Parasut\ParasutClient
 */
class Parasut extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'parasut';
    }
}
