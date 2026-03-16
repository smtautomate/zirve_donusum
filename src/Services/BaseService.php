<?php

namespace ZirveDonusum\Services;

use ZirveDonusum\HttpClient;

abstract class BaseService
{
    protected HttpClient $http;

    /**
     * Her service'in API prefix'i — override edilecek.
     * eMikro'da endpoint'ler genelde /home/, /einvoice/, /edespatch/ vb. altında.
     * Sen endpoint'leri verdikçe güncellenecek.
     */
    protected string $prefix = '';

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    protected function endpoint(string $path = ''): string
    {
        $base = rtrim($this->prefix, '/');
        $path = ltrim($path, '/');

        return $base ? "{$base}/{$path}" : "/{$path}";
    }
}
