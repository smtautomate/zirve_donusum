<?php

namespace ZirveDonusum\Services;

use ZirveDonusum\HttpClient;

abstract class BaseService
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * /cp/{accountId}/{path} oluşturur
     */
    protected function cp(string $path): string
    {
        return $this->http->cpPath($path);
    }
}
