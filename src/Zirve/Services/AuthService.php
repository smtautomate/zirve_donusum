<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Zirve Portal kimlik dogrulama servisi.
 * Login, iki adimli dogrulama, captcha ve oturum yonetimi islemleri.
 */
class AuthService extends BaseService
{
    // ─── Giris & Oturum ───────────────────────────────────────────────

    /**
     * Kullanici girisi yapar.
     *
     * @param string $username Kullanici adi
     * @param string $password Sifre
     * @return array accessToken ve parentCustomerId iceren yanit
     */
    public function login(string $username, string $password): array
    {
        return $this->http->post('/auth/signin', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Iki adimli dogrulama kodunu onaylar.
     *
     * @param string $code SMS veya e-posta ile gonderilen dogrulama kodu
     * @return array Dogrulama sonucu
     */
    public function verifyTwoStep(string $code): array
    {
        return $this->http->post('/auth/verifyTwoStepCode', [
            'code' => $code,
        ]);
    }

    /**
     * Suresi dolmus oturumlari temizler.
     *
     * @return array Silme islemi sonucu
     */
    public function deleteExpired(): array
    {
        return $this->http->post('/auth/deleteExpired');
    }

    // ─── Captcha & Guvenlik ───────────────────────────────────────────

    /**
     * Captcha dogrulamasi yapar.
     *
     * @param string $captcha Kullanicinin girdigi captcha degeri
     * @return array Dogrulama sonucu
     */
    public function verifyCaptcha(string $captcha): array
    {
        return $this->http->post('/auth/verify-captcha', [
            'captcha' => $captcha,
        ]);
    }

    /**
     * Kullanici hesabini kilitler.
     *
     * @return array Kilitleme islemi sonucu
     */
    public function lockUser(): array
    {
        return $this->http->post('/auth/lockUser');
    }

    // ─── Dogrulama Kodu ───────────────────────────────────────────────

    /**
     * Dogrulama kodunu tekrar gonderir.
     *
     * @return array Gonderim sonucu
     */
    public function resendVerificationCode(): array
    {
        return $this->http->post('/auth/sendVerificationCodeAgain');
    }
}
