<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Zirve Portal ortak veri servisi.
 * Tevkifat turleri, duraksama turleri, doviz kurlari ve entegrator yapilandirmasi.
 */
class CommonService extends BaseService
{
    // ─── Tip & Tanimlamalar ───────────────────────────────────────────

    /**
     * Tum duraksama (stopaj) tiplerini getirir.
     *
     * @return array Duraksama tipi listesi
     */
    public function allStoppageTypes(): array
    {
        return $this->http->get('/common/allStoppageTypes');
    }

    /**
     * Tum tevkifat tiplerini getirir.
     *
     * @return array Tevkifat tipi listesi
     */
    public function allWithholdingTypes(): array
    {
        return $this->http->get('/common/allWithholdingTypes');
    }

    // ─── Doviz & Kur ─────────────────────────────────────────────────

    /**
     * Belirtilen doviz kodunun guncel kur degerini getirir.
     *
     * @param string $code Doviz kodu (orn: USD, EUR)
     * @return array Kur degeri bilgileri
     */
    public function getCurrencyValue(string $code): array
    {
        return $this->http->get("/common/getCurrencyValue/{$code}");
    }

    // ─── Entegrator Yapilandirmasi ────────────────────────────────────

    /**
     * Entegrator yapilandirmasini getirir.
     *
     * @return array Entegrator ayarlari
     */
    public function getEntegratorConf(): array
    {
        return $this->http->get('/common/getEntegratorConf');
    }

    // ─── Yeniden Yukleme ──────────────────────────────────────────────

    /**
     * Belirtilen ID icin tum verileri yeniden yukler.
     *
     * @param int $id Yeniden yuklenecek kayit ID
     * @return array Yukleme sonucu
     */
    public function reloadAll(int $id): array
    {
        return $this->http->post("/common/reloadAll/{$id}");
    }
}
