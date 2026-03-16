<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Serbest Meslek Makbuzu (SMM / e-SMM) islemleri servisi.
 * Makbuz kopyalama, fiyat listesi, kaydetme ve durum guncelleme.
 */
class VoucherService extends BaseService
{
    /**
     * Makbuzu kopyalayarak duzenleme modunda acar.
     *
     * @param array $data Kopyalanacak makbuz verileri
     * @return array Duzenleme icin hazir makbuz verisi
     */
    public function copyEdit(array $data): array
    {
        return $this->http->post('/voucherTmp/copyEditVoucher', $data);
    }

    /**
     * E-SMM fiyat listesini getirir.
     *
     * @return array Fiyat listesi
     */
    public function getESmmPriceList(): array
    {
        return $this->http->get('/voucherTmp/getESmmPriceList');
    }

    /**
     * Kiymetli maden bilgilerini getirir.
     *
     * @return array Kiymetli maden verileri
     */
    public function getKiymetliMaden(): array
    {
        return $this->http->get('/voucherTmp/getKiymetliMaden');
    }

    /**
     * Birden fazla makbuzu toplu olarak kaydeder.
     *
     * @param int   $id   Makbuz grubu ID
     * @param array $data Makbuz verileri
     * @return array Kayit sonucu
     */
    public function saveMultiple(int $id, array $data): array
    {
        return $this->http->post("/voucherTmp/saveMultiple/{$id}", $data);
    }

    /**
     * Makbuz onizlemesi getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Belge onizleme verisi
     */
    public function showDocument(array $query): array
    {
        return $this->http->get('/voucherTmp/showDocument', $query);
    }

    /**
     * Mevcut makbuzu gunceller.
     *
     * @param array $data Guncellenecek makbuz verileri
     * @return array Guncelleme sonucu
     */
    public function update(array $data): array
    {
        return $this->http->post('/voucherTmp/update', $data);
    }

    /**
     * Tum makbuzlarin islem durumunu toplu olarak degistirir.
     *
     * @param array $query Sorgu parametreleri (durum bilgisi vb.)
     * @return array Durum degisikligi sonucu
     */
    public function changeAllProcessState(array $query): array
    {
        return $this->http->get('/eSmmArchiveVoucherStatus/changeAllProcessState', $query);
    }
}
