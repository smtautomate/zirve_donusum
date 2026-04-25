<?php

namespace ZirveDonusum\Parasut\Services;

use ZirveDonusum\Parasut\HttpClient;

/**
 * Tum Parasut servis siniflarinin temel sinifi.
 * OAuth2 Bearer authentication HttpClient uzerinden saglanir.
 */
abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
