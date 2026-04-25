<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Tedarikci (Contact) - account_type=supplier.
 */
class SupplierService extends BaseResourceService
{
    protected string $endpoint = 'contacts';

    public function index(array $query = []): array
    {
        $query['filter']['account_type'] = $query['filter']['account_type'] ?? 'supplier';
        return parent::index($query);
    }
}
