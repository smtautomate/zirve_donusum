<?php

namespace ZirveDonusum\Gib;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Gib\Auth\TokenManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

/**
 * GİB e-Arşiv Portal HTTP client.
 *
 * Mikro (session/cookie) ve Zirve (JWT) den tamamen farklı:
 * - Tüm istekler form-encoded (JSON body yok)
 * - Login: POST /earsiv-services/assos-login (form fields)
 * - Ana API: POST /earsiv-services/dispatch (callid + token + cmd + pageName + jp)
 * - jp parametresi JSON STRING olarak gönderilir, object değil
 * - Download: GET /earsiv-services/download (query params)
 */
class HttpClient
{
    private GuzzleClient $http;
    private TokenManager $tokenManager;
    private string $baseUrl;
    private string $username;
    private string $password;
    private bool $testMode;
    private int $timeout;
    private bool $authenticated = false;
    private ?string $token = null;

    public function __construct(array $config)
    {
        $this->testMode = $config['test_mode'] ?? false;
        $this->baseUrl = $this->testMode
            ? 'https://earsivportaltest.efatura.gov.tr'
            : ($config['base_url'] ?? 'https://earsivportal.efatura.gov.tr');
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/gib-portal';
        $this->tokenManager = new TokenManager(
            ($config['cache_token'] ?? true) ? $cacheDir : null
        );

        // Cache'den token yükle
        $cached = $this->tokenManager->load();
        if ($cached) {
            $this->token = $cached['token'];
            $this->authenticated = true;
        }

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? false, // GİB zaman zaman SSL sorunu yaşıyor
            'http_errors' => false,
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * GİB e-Arşiv Portal'a login olur.
     * POST /earsiv-services/assos-login
     * form_params: assoscmd, userid, sifre, sifre2, parola
     *
     * Test modunda assoscmd=login, production'da assoscmd=anologin
     * sifre, sifre2 ve parola alanlarının üçü de aynı şifre.
     */
    public function login(): bool
    {
        try {
            $assoscmd = $this->testMode ? 'login' : 'anologin';

            $response = $this->http->post('/earsiv-services/assos-login', [
                'form_params' => [
                    'assoscmd' => $assoscmd,
                    'rtype' => 'json',
                    'userid' => $this->username,
                    'sifre' => $this->password,
                    'sifre2' => $this->password,
                    'parola' => $this->password,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);

            if ($statusCode !== 200 || !$body) {
                throw new AuthenticationException(
                    "GİB login failed with status {$statusCode}: {$rawBody}",
                    $statusCode,
                    $body ?? []
                );
            }

            $token = $body['token'] ?? null;

            if (!$token) {
                throw new AuthenticationException(
                    'GİB login response does not contain token',
                    $statusCode,
                    $body
                );
            }

            $this->token = $token;
            $this->authenticated = true;

            // Token'ı cache'le (1 saat TTL)
            $this->tokenManager->save($this->token);

            return true;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'GİB login request failed: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * GİB test portalından test kullanıcı bilgilerini al.
     * POST /earsiv-services/esign
     * form_params: assoscmd=kullaniciOner, rtype=json
     *
     * @return array Test kullanıcı bilgileri (userid, sifre vb.)
     */
    public function getTestUser(): array
    {
        try {
            $response = $this->http->post('/earsiv-services/esign', [
                'form_params' => [
                    'assoscmd' => 'kullaniciOner',
                    'rtype' => 'json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);

            if ($statusCode !== 200 || !$body) {
                throw new ApiException(
                    "GİB getTestUser failed with status {$statusCode}: {$rawBody}",
                    $statusCode,
                    '/earsiv-services/esign',
                    $body ?? []
                );
            }

            return $body;
        } catch (GuzzleException $e) {
            throw new ApiException(
                'GİB getTestUser request failed: ' . $e->getMessage(),
                $e->getCode(),
                '/earsiv-services/esign',
                [],
                $e
            );
        }
    }

    public function ensureAuthenticated(): void
    {
        if (!$this->authenticated) {
            $this->login();
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->token = null;
        $this->tokenManager->clear();
    }

    // ─── Dispatch (Main API pattern) ─────────────────────────────────

    /**
     * GİB e-Arşiv dispatch API.
     * POST /earsiv-services/dispatch
     * form_params: callid (UUID v4), token, cmd, pageName, jp (JSON string!)
     *
     * jp parametresi mutlaka JSON STRING olmalı, object değil.
     * Auth hatası tespit edilirse yeniden login olup bir kez daha dener.
     *
     * @param string $cmd Komut adı (ör: EARSIV_PORTAL_FATURA_OLUSTUR)
     * @param string $pageName Sayfa adı (ör: RG_BASITFATURA)
     * @param array|string $jpData JP verisi — array ise json_encode edilir, string ise olduğu gibi gönderilir
     * @return array Yanıt verisi (response['data'] ?? response)
     */
    public function dispatch(string $cmd, string $pageName, $jpData = '{}'): array
    {
        $this->ensureAuthenticated();

        // jp parametresini JSON string'e çevir
        $jp = is_array($jpData)
            ? json_encode($jpData, JSON_UNESCAPED_UNICODE)
            : (string) $jpData;

        try {
            $formParams = [
                'callid' => $this->generateCallId(),
                'token' => $this->token,
                'cmd' => $cmd,
                'pageName' => $pageName,
                'jp' => $jp,
            ];

            $response = $this->http->post('/earsiv-services/dispatch', [
                'form_params' => $formParams,
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);

            // Auth hatası kontrolü — GİB login sayfasına redirect döner
            if ($this->isAuthFailure($statusCode, $rawBody, $body)) {
                $this->clearAndRelogin();

                // Yeni token ile tekrar dene
                $formParams['callid'] = $this->generateCallId();
                $formParams['token'] = $this->token;

                $response = $this->http->post('/earsiv-services/dispatch', [
                    'form_params' => $formParams,
                ]);

                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
                $body = json_decode($rawBody, true);
            }

            if ($statusCode >= 400) {
                throw new ApiException(
                    "GİB dispatch failed: HTTP {$statusCode}",
                    $statusCode,
                    '/earsiv-services/dispatch',
                    $body ?? ['raw' => $rawBody]
                );
            }

            if (!$body) {
                throw new ApiException(
                    'GİB dispatch returned invalid JSON',
                    $statusCode,
                    '/earsiv-services/dispatch',
                    ['raw' => $rawBody]
                );
            }

            return $body['data'] ?? $body;
        } catch (GuzzleException $e) {
            throw new ApiException(
                'GİB dispatch request failed: ' . $e->getMessage(),
                $e->getCode(),
                '/earsiv-services/dispatch',
                [],
                $e
            );
        }
    }

    // ─── Download ────────────────────────────────────────────────────

    /**
     * e-Arşiv fatura belgesi indir (ZIP formatında).
     * GET /earsiv-services/download
     *
     * @param string $ettn Faturanın ETTN (UUID) değeri
     * @param string $onayDurumu Onay durumu (varsayılan: Onaylandı)
     * @return string Ham binary içerik (ZIP dosyası)
     */
    public function download(string $ettn, string $onayDurumu = 'Onaylandı'): string
    {
        $this->ensureAuthenticated();

        try {
            $response = $this->http->get('/earsiv-services/download', [
                'query' => [
                    'token' => $this->token,
                    'ettn' => $ettn,
                    'onayDurumu' => $onayDurumu,
                    'belgeTip' => 'FATURA',
                    'cmd' => 'EARSIV_PORTAL_BELGE_INDIR',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            // Auth hatası kontrolü
            if ($this->isAuthFailure($statusCode, $rawBody)) {
                $this->clearAndRelogin();

                $response = $this->http->get('/earsiv-services/download', [
                    'query' => [
                        'token' => $this->token,
                        'ettn' => $ettn,
                        'onayDurumu' => $onayDurumu,
                        'belgeTip' => 'FATURA',
                        'cmd' => 'EARSIV_PORTAL_BELGE_INDIR',
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            if ($statusCode >= 400) {
                throw new ApiException(
                    "GİB download failed: HTTP {$statusCode}",
                    $statusCode,
                    '/earsiv-services/download'
                );
            }

            return $rawBody;
        } catch (GuzzleException $e) {
            throw new ApiException(
                'GİB download request failed: ' . $e->getMessage(),
                $e->getCode(),
                '/earsiv-services/download',
                [],
                $e
            );
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * UUID v4 formatında benzersiz callid üret.
     */
    private function generateCallId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * GİB auth hatası kontrolü.
     * GİB session expire olduğunda login sayfasına redirect veya
     * response içinde login URL'i döner.
     */
    private function isAuthFailure(int $statusCode, string $rawBody, ?array $body = null): bool
    {
        if (in_array($statusCode, [401, 403])) {
            return true;
        }

        // GİB bazen HTML login sayfası döner
        if (str_contains($rawBody, 'assos-login') || str_contains($rawBody, 'login')) {
            // JSON response ise ve data içeriyorsa auth hatası değildir
            if ($body && isset($body['data'])) {
                return false;
            }
            // HTML veya redirect ise auth hatası
            if (!$body) {
                return true;
            }
        }

        return false;
    }

    /**
     * Token'ı temizle ve yeniden login ol.
     */
    private function clearAndRelogin(): void
    {
        $this->authenticated = false;
        $this->token = null;
        $this->tokenManager->clear();
        $this->login();
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
