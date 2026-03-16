<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Stok islemleri servisi.
 * Stok CRUD, kodlar, birimler ve toplu islemler.
 */
class StockService extends BaseService
{
    // ─── Listeleme & Sorgulama ──────────────────────────────────────

    /**
     * Stoklari sayfalanmis olarak listeler.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basina kayit sayisi
     * @return array Stok listesi
     */
    public function list(int $page = 0, int $size = 10): array
    {
        return $this->http->post('/stock/getStocks', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Tum stoklari listeler.
     *
     * @return array Tam stok listesi
     */
    public function listAll(): array
    {
        return $this->http->get('/stock/listStocks');
    }

    /**
     * Stok koduna gore stok getirir.
     *
     * @param string $code Stok kodu
     * @return array Stok bilgisi
     */
    public function getByCode(string $code): array
    {
        return $this->http->get('/stock/getByCode', ['code' => $code]);
    }

    // ─── Kod Listeleri ──────────────────────────────────────────────

    /**
     * Stok kodlarini getirir.
     *
     * @return array Stok kod listesi
     */
    public function getCodes(): array
    {
        return $this->http->post('/stock/getCodes');
    }

    /**
     * Para birimi kodlarini getirir.
     *
     * @return array Para birimi kod listesi
     */
    public function getCurrencyCodes(): array
    {
        return $this->http->get('/stock/getCurrencyCodes');
    }

    /**
     * Muafiyet kodlarini getirir.
     *
     * @return array Muafiyet kod listesi
     */
    public function getExemptionCodes(): array
    {
        return $this->http->get('/stock/getExemptionCodes');
    }

    /**
     * Birim kodlarini getirir.
     *
     * @return array Birim kod listesi
     */
    public function getUnitCodes(): array
    {
        return $this->http->get('/stock/getUnitCodes');
    }

    /**
     * GTIP kodlarini musteri ID'sine gore getirir.
     *
     * @param int $customerId Musteri ID
     * @return array GTIP kod listesi
     */
    public function getGtip(int $customerId): array
    {
        return $this->http->get('/stock/getGtip', ['customerId' => $customerId]);
    }

    // ─── Kaydetme & Silme ───────────────────────────────────────────

    /**
     * Stok kaydeder.
     *
     * @param array $data Stok verileri
     * @return array Kayit sonucu
     */
    public function save(array $data): array
    {
        return $this->http->post('/stock/save', $data);
    }

    /**
     * Birden fazla stogu toplu kaydeder.
     *
     * @param array $data Stok verileri dizisi
     * @return array Kayit sonucu
     */
    public function saveMultiple(array $data): array
    {
        return $this->http->post('/stock/saveMultiple', $data);
    }

    /**
     * Stogu siler.
     *
     * @param int $id Stok ID
     * @return array Silme sonucu
     */
    public function delete(int $id): array
    {
        return $this->http->post("/stock/delete/{$id}", []);
    }

    /**
     * Birden fazla stogu toplu siler.
     *
     * @param array $data Silinecek stok ID'leri
     * @return array Silme sonucu
     */
    public function deleteMultiple(array $data): array
    {
        return $this->http->post('/stock/deleteMultiple', $data);
    }
}
