<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Giden fatura (outbox) islemleri servisi.
 * Gonderilen faturalarin HTML goruntulemesi, durum kodlari ve zarf sorgulama.
 */
class OutboxService extends BaseService
{
    /**
     * Fatura durum kodlarini getirir.
     *
     * @return array Durum kodlari listesi
     */
    public function getCodes(): array
    {
        return $this->http->post('/outbox/getCodes');
    }

    /**
     * Fatura HTML listesini sayfalanmis olarak getirir.
     *
     * @param array $data Sayfalama ve filtre verileri (page, size vb.)
     * @return array HTML fatura listesi
     */
    public function downloadHtml(array $data): array
    {
        return $this->http->post('/outbox/downloadMedia/html', $data);
    }

    /**
     * Tek bir faturanin HTML icerigini indirir.
     *
     * @param array $query Sorgu parametreleri (uuid vb.)
     * @return string Ham HTML icerigi
     */
    public function getHtml(array $query): string
    {
        return $this->http->download('/outbox/downloadMedia/html', $query);
    }

    /**
     * Zarf durumunu sorgular.
     *
     * @param array $data Zarf sorgu verileri
     * @return array Zarf durumu sonucu
     */
    public function queryEnvelope(array $data): array
    {
        return $this->http->post('/outbox/queryEnvelope', $data);
    }
}
