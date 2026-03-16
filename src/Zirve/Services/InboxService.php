<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Gelen fatura (inbox) islemleri servisi.
 * Gelen faturalarin HTML goruntulemesi ve indirme islemleri.
 */
class InboxService extends BaseService
{
    /**
     * Gelen fatura HTML listesini getirir.
     *
     * @param array $data Sayfalama ve filtre verileri
     * @return array HTML fatura listesi
     */
    public function downloadHtml(array $data): array
    {
        return $this->http->post('/inbox/downloadMedia/html', $data);
    }

    /**
     * Tek bir gelen faturanin HTML icerigini indirir.
     *
     * @param array $query Sorgu parametreleri (uuid vb.)
     * @return string Ham HTML icerigi
     */
    public function getHtml(array $query): string
    {
        return $this->http->download('/inbox/downloadMedia/html', $query);
    }
}
