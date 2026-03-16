<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Kredi/kontör islemleri servisi.
 * Kredi listeleme, tarihe gore sorgulama ve hareket raporu indirme.
 */
class CreditService extends BaseService
{
    /**
     * Kredileri sayfalanmis olarak listeler.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basina kayit sayisi
     * @return array Kredi listesi
     */
    public function list(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/credit/getCredits', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Tarihe gore kredi bilgisini getirir.
     *
     * @param array $query Tarih sorgu parametreleri
     * @return array Kredi bilgisi
     */
    public function getByDate(array $query): array
    {
        return $this->http->get('/credit/getCreditByDate', $query);
    }

    /**
     * Suresi dolacak kredileri getirir.
     *
     * @return array Suresi dolacak kredi listesi
     */
    public function getExpireCredits(): array
    {
        return $this->http->get('/credit/getExpireCredits');
    }

    /**
     * Tolerans gun sayisini getirir.
     *
     * @return array Tolerans gun bilgisi
     */
    public function getTolerateDay(): array
    {
        return $this->http->get('/credit/getTolerateDay');
    }

    /**
     * Kredi hareketlerini XLS formatinda indirir.
     *
     * @return string Ham dosya icerigi
     */
    public function downloadMovementsXls(): string
    {
        return $this->http->download('/credit/getCreditMovementsXls');
    }
}
