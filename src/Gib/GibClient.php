<?php

namespace ZirveDonusum\Gib;

use ZirveDonusum\Gib\Services\AuthService;
use ZirveDonusum\Gib\Services\InvoiceService;
use ZirveDonusum\Gib\Services\UserService;

/**
 * GİB e-Arşiv Portal API Client.
 *
 * GİB'in e-Arşiv portalı ile doğrudan iletişim kurar (entegratör aracılığı olmadan).
 * Fatura oluşturma, listeleme, görüntüleme ve indirme işlemlerini yönetir.
 *
 * Kullanım:
 *   $client = new GibClient([
 *       'username' => '1234567890',  // VKN veya TCKN
 *       'password' => 'sifre',
 *       'test_mode' => true,         // Test ortamı (varsayılan: false)
 *   ]);
 *
 *   // Giriş yap
 *   $client->login();
 *
 *   // Fatura oluştur
 *   $client->invoices()->createDraft([...]);
 *
 *   // Taslakları listele
 *   $faturalar = $client->invoices()->listDrafts('01/01/2026', '31/12/2026');
 *
 *   // Kullanıcı bilgileri
 *   $bilgiler = $client->users()->getInfo();
 */
class GibClient
{
    private HttpClient $http;
    private ?AuthService $authService = null;
    private ?InvoiceService $invoiceService = null;
    private ?UserService $userService = null;

    /**
     * GibClient oluştur.
     *
     * @param array $config Yapılandırma:
     *   - username: string — GİB kullanıcı adı (VKN/TCKN)
     *   - password: string — GİB şifresi
     *   - test_mode: bool — Test ortamı (varsayılan: false)
     *   - base_url: string — Özel base URL (isteğe bağlı)
     *   - timeout: int — HTTP timeout saniye (varsayılan: 30)
     *   - cache_dir: string — Token cache dizini (isteğe bağlı)
     *   - cache_token: bool — Token caching (varsayılan: true)
     */
    public function __construct(array $config)
    {
        $this->http = new HttpClient($config);
    }

    // ─── Servis Erişim Noktaları ──────────────────────────────────────

    /**
     * Kimlik doğrulama servisi.
     *
     * Farklı kimlik bilgileriyle giriş yapma ve test kullanıcısı alma
     * işlemleri için kullanılır.
     */
    public function auth(): AuthService
    {
        return $this->authService ??= new AuthService($this->http);
    }

    /**
     * Fatura servisi.
     *
     * E-Arşiv fatura oluşturma, listeleme, görüntüleme ve indirme
     * işlemleri için ana servis noktası.
     */
    public function invoices(): InvoiceService
    {
        return $this->invoiceService ??= new InvoiceService($this->http);
    }

    /**
     * Kullanıcı servisi.
     *
     * Oturum açmış kullanıcının mükellef bilgilerini sorgulamak için kullanılır.
     */
    public function users(): UserService
    {
        return $this->userService ??= new UserService($this->http);
    }

    // ─── Doğrudan Erişim ──────────────────────────────────────────────

    /**
     * Doğrudan HTTP client erişimi.
     *
     * Özel GİB endpoint'leri çağırmak veya düşük seviyeli
     * işlemler yapmak için kullanılır.
     */
    public function http(): HttpClient
    {
        return $this->http;
    }

    /**
     * GİB portalına varsayılan kimlik bilgileri ile giriş yap.
     *
     * Yapılandırmada verilen username/password ile oturum açar.
     *
     * @return bool Giriş başarılı ise true
     *
     * @throws \ZirveDonusum\Exceptions\AuthenticationException Giriş başarısız
     */
    public function login(): bool
    {
        return $this->http->login();
    }

    /**
     * GİB portalından çıkış yap.
     *
     * Mevcut session token'ı geçersiz kılar ve cache'i temizler.
     */
    public function logout(): void
    {
        $this->http->logout();
    }

    /**
     * GİB bağlantısını test et.
     *
     * Giriş yapıp kullanıcı bilgilerini çekerek bağlantının
     * sağlıklı olduğunu doğrular.
     *
     * @return bool Bağlantı başarılı ise true
     */
    public function testConnection(): bool
    {
        try {
            $this->http->login();
            $this->users()->getInfo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Test modunda mı kontrol et.
     *
     * Test modu aktifse GİB'in test portalı (earsivportaltest.efatura.gov.tr)
     * kullanılır, aksi halde gerçek portal (earsivportal.efatura.gov.tr).
     *
     * @return bool Test modundaysa true
     */
    public function isTestMode(): bool
    {
        return $this->http->isTestMode();
    }
}
