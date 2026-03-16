<?php

namespace ZirveDonusum\Services;

/**
 * Referans / Lookup Verileri
 * İl, vergi dairesi gibi sabit veriler (harici API dahil)
 *
 * Gerçek endpoint'ler:
 *   GET https://eportal-api.atros.com.tr/api/v1/cities/with-taxoffices
 *   GET https://eportal-api.atros.com.tr/api/v1/iys/permission/status
 */
class LookupService extends BaseService
{
    private string $atrosApiBase = 'https://eportal-api.atros.com.tr/api/v1';

    /**
     * İl listesi ve vergi daireleri
     */
    public function getCitiesWithTaxOffices(): array
    {
        return $this->http->get($this->atrosApiBase . '/cities/with-taxoffices');
    }

    /**
     * İYS izin durumu
     */
    public function getIysPermissionStatus(): array
    {
        return $this->http->get($this->atrosApiBase . '/iys/permission/status');
    }
}
