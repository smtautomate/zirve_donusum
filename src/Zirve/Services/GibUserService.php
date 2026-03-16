<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * GIB (Gelir Idaresi Baskanligi) kullanici islemleri servisi.
 * e-Fatura/e-Arsiv mukellef sorgulama ve alias listeleme.
 */
class GibUserService extends BaseService
{
    /**
     * Identifier (VKN/TCKN) ile GIB kullanicisini getirir.
     *
     * @param string $identifier Kullanici tanimlayicisi (VKN/TCKN)
     * @return array GIB kullanici bilgisi
     */
    public function fetch(string $identifier): array
    {
        return $this->http->get("/gibuser/fetch/{$identifier}");
    }

    /**
     * Musterinin alias (etiket) listesini getirir.
     *
     * @return array Alias listesi
     */
    public function getCustomerAliasList(): array
    {
        return $this->http->get('/gibuser/getCustomerAliasList');
    }

    /**
     * Identifier ile GIB kullanicilarini sorgular.
     *
     * @param array $data Sorgu verileri (identifier vb.)
     * @return array GIB kullanici listesi
     */
    public function getByIdentifier(array $data): array
    {
        return $this->http->post('/gibuser/getGIBUsersByIdentifier', $data);
    }
}
