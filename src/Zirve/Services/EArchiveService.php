<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * E-Arsiv islemleri servisi.
 * E-Arsiv faturalarinin HTML goruntulemesi ve durum kodlari.
 */
class EArchiveService extends BaseService
{
    /**
     * E-Arsiv fatura HTML listesini getirir.
     *
     * @param array $data Sayfalama ve filtre verileri
     * @return array HTML fatura listesi
     */
    public function downloadHtml(array $data): array
    {
        return $this->http->post('/eArchive/downloadMedia/html', $data);
    }

    /**
     * E-Arsiv durum kodlarini getirir.
     *
     * @return array Durum kodlari listesi
     */
    public function getCodes(): array
    {
        return $this->http->post('/eArchive/getCodes');
    }
}
