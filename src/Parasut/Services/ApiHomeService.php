<?php

namespace ZirveDonusum\Parasut\Services;

/**
 * API Home - / (root). Sirket ID gerektirmez, ilk dogrulama icin idealdir.
 */
class ApiHomeService extends BaseService
{
    public function index(): array
    {
        return $this->http->getRoot('');
    }

    /**
     * /me endpoint'i (kullanici bilgisi).
     */
    public function me(): array
    {
        return $this->http->getRoot('me');
    }
}
