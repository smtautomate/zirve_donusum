<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Irsaliye (despatch) islemleri servisi.
 * Toplu irsaliye kaydetme ve gelen irsaliye HTML indirme.
 */
class DespatchService extends BaseService
{
    /**
     * Birden fazla irsaliyeyi toplu olarak kaydeder.
     *
     * @param array $data Irsaliye verileri
     * @return array Kayit sonucu
     */
    public function saveMultiple(array $data): array
    {
        return $this->http->post('/despatches/saveMultiple', $data);
    }

    /**
     * Gelen irsaliyenin HTML icerigini indirir.
     *
     * @param array $query Sorgu parametreleri (uuid vb.)
     * @return string Ham HTML icerigi
     */
    public function inboxDownloadHtml(array $query): string
    {
        return $this->http->download('/despatchInbox/downloadMedia/html', $query);
    }
}
