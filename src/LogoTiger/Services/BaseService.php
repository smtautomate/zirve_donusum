<?php

namespace ZirveDonusum\LogoTiger\Services;

use ZirveDonusum\LogoTiger\HttpClient;

/**
 * Logo Tiger REST API servisleri icin temel sinif.
 * firmaNo / donemNo HttpClient tarafindan otomatik enjekte edilir.
 */
abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Aktif firma numarasi.
     */
    protected function firma(): string
    {
        return $this->http->getFirmaNo();
    }

    /**
     * Aktif donem numarasi.
     */
    protected function donem(): string
    {
        return $this->http->getDonemNo();
    }
}
