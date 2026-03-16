<?php

namespace ZirveDonusum\Services;

/**
 * Ürün / Hizmet İşlemleri
 *
 * Gerçek endpoint'ler:
 *   GET /cp/{accountId}/Product/Units
 *   GET /cp/{accountId}/Product/ProductListCashAccount
 */
class ProductService extends BaseService
{
    /**
     * Ürün/Hizmet listesi (sayfalı)
     *
     * @param array $filters:
     *   - search: Arama
     *   - pageNumber: Sayfa (varsayılan: 1)
     *   - pageSize: Sayfa başına kayıt (varsayılan: 20)
     */
    public function list(array $filters = []): array
    {
        $defaults = [
            'pageNumber' => 1,
            'pageSize' => 20,
            'search' => '',
        ];

        return $this->http->get(
            $this->cp('Product/ProductListCashAccount'),
            array_merge($defaults, $filters)
        );
    }

    /**
     * Birim listesi (Adet, Kg, Litre vb.)
     */
    public function getUnits(): array
    {
        return $this->http->get($this->cp('Product/Units'));
    }
}
