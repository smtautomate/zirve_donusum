<?php

namespace ZirveDonusum\Gib\Services;

/**
 * GİB e-Arşiv Portal kullanıcı servisi.
 *
 * Giriş yapmış kullanıcının GİB'deki bilgilerini sorgular.
 * VKN/TCKN, unvan, vergi dairesi gibi mükellef bilgilerini döndürür.
 */
class UserService extends BaseService
{
    /**
     * Kullanıcı bilgilerini getir.
     *
     * GİB portalında oturum açmış kullanıcının mükellef bilgilerini döndürür.
     * Dönen veriler: VKN/TCKN, unvan, vergi dairesi, adres vb.
     *
     * @return array Kullanıcı/mükellef bilgileri
     *
     * @throws \ZirveDonusum\Exceptions\ApiException Bilgiler alınamadığında
     */
    public function getInfo(): array
    {
        return $this->http->dispatch(
            'EARSIV_PORTAL_KULLANICI_BILGILERI_GETIR',
            'RG_KULLANICI',
            '{}'
        );
    }
}
