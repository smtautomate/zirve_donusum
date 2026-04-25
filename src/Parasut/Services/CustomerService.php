<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Musteri (Contact) - account_type=customer ile filtrelenir.
 * Endpoint: /{companyId}/contacts
 */
class CustomerService extends BaseResourceService
{
    protected string $endpoint = 'contacts';

    public function index(array $query = []): array
    {
        $query['filter']['account_type'] = $query['filter']['account_type'] ?? 'customer';
        return parent::index($query);
    }
}
