<?php

namespace ZirveDonusum\Uyumsoft\Services;

use ZirveDonusum\Uyumsoft\HttpClient;

abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
