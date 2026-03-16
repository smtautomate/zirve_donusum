<?php

namespace ZirveDonusum\Gib\Services;

use ZirveDonusum\Gib\HttpClient;

/**
 * GİB e-Arşiv Portal servislerinin temel sınıfı.
 * Tüm alt servisler bu sınıftan türer ve HTTP client'a erişim sağlar.
 */
abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
