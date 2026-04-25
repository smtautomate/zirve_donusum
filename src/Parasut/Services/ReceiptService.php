<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Tahsilat / Tediye fisi - /transactions
 * Parasut'ta receipt'lar transaction olarak saklanir (cash_account_activities).
 */
class ReceiptService extends BaseResourceService
{
    protected string $endpoint = 'transactions';
}
