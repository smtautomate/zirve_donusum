<?php

namespace ZirveDonusum\Services;

/**
 * Müşteri İşlemleri
 *
 * Gerçek endpoint'ler:
 *   GET /cp/{accountId}/Customer/GetCustomerListPaginated
 */
class CustomerService extends BaseService
{
    /**
     * Müşteri listesi (sayfalı, filtreli)
     *
     * @param array $filters:
     *   - title: Firma adı
     *   - ad: Ad
     *   - soyad: Soyad
     *   - tcknOrVkn: TCKN veya VKN
     *   - pageNumber: Sayfa (varsayılan: 1)
     *   - pageSize: Sayfa başına kayıt (varsayılan: 20)
     */
    public function list(array $filters = []): array
    {
        $defaults = [
            'accountId' => $this->http->getAccountId(),
            'title' => '',
            'ad' => '',
            'soyad' => '',
            'tcknOrVkn' => '',
            'pageNumber' => 1,
            'pageSize' => 20,
        ];

        return $this->http->get(
            $this->cp('Customer/GetCustomerListPaginated'),
            array_merge($defaults, $filters)
        );
    }
}
