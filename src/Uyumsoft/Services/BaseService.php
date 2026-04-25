<?php

namespace ZirveDonusum\Uyumsoft\Services;

use ZirveDonusum\Uyumsoft\HttpClient;

/**
 * Uyumsoft servis siniflarinin temel sinifi.
 */
abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
