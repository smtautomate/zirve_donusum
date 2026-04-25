<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * Asenkron is takibi - /trackable_jobs
 * E-Fatura/E-Arsiv sonuclari async donerken bu endpoint ile durum sorgulanir.
 */
class TrackableJobService extends BaseService
{
    public function show(int|string $id, array $query = []): array
    {
        return $this->http->get("trackable_jobs/{$id}", $query);
    }
}
