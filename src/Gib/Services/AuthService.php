<?php

namespace ZirveDonusum\Gib\Services;

/**
 * GİB e-Arşiv Portal kimlik doğrulama servisi.
 *
 * GİB portalına giriş/çıkış ve test kullanıcı işlemlerini yönetir.
 * Token bazlı session yönetimi kullanır (cookie/JWT yok).
 */
class AuthService extends BaseService
{
    /**
     * GİB portalına giriş yap.
     *
     * HttpClient üzerindeki login() metodunu kullanır ancak
     * farklı kimlik bilgileri ile giriş yapma imkanı sağlar.
     *
     * @param string $username GİB kullanıcı adı (VKN/TCKN)
     * @param string $password GİB şifresi
     * @return bool Giriş başarılı ise true
     *
     * @throws \ZirveDonusum\Exceptions\AuthenticationException Kimlik doğrulama başarısız
     */
    public function login(string $username, string $password): bool
    {
        return $this->http->login($username, $password);
    }

    /**
     * Test ortamı için GİB'in sağladığı test kullanıcı bilgilerini al.
     *
     * Sadece test modunda çalışır.
     * GİB test portalı üzerinden rastgele bir test kullanıcısı döndürür.
     *
     * @return array{userId: string, password: string} Test kullanıcı bilgileri
     *
     * @throws \ZirveDonusum\Exceptions\ApiException Test kullanıcısı alınamadığında
     */
    public function getTestUser(): array
    {
        return $this->http->getTestUser();
    }
}
