<?php

namespace ZirveDonusum\Zirve\Services;

use ZirveDonusum\Zirve\HttpClient;

/**
 * Tum servis siniflarinin temel sinifi.
 * JWT Bearer authentication HttpClient uzerinden saglanir.
 */
abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
