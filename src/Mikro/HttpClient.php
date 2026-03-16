<?php

namespace ZirveDonusum\Mikro;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Mikro\Auth\SessionManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

class HttpClient
{
    private GuzzleClient $http;
    private SessionManager $sessionManager;
    private CookieJar $cookieJar;
    private string $baseUrl;
    private string $email;
    private string $password;
    private int $timeout;
    private bool $authenticated = false;

    /**
     * eMikro'nun tüm API çağrıları /cp/{accountId}/... altında.
     * Login sonrası status endpoint'inden veya redirect'ten alınır.
     */
    private ?string $accountId = null;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://eportal.mikrogrup.com', '/');
        $this->email = $config['email'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/emikro';
        $this->sessionManager = new SessionManager(
            ($config['cache_session'] ?? true) ? $cacheDir : null
        );

        $this->cookieJar = new CookieJar();

        // Cache'den session ve accountId yükle
        $cached = $this->sessionManager->load();
        if ($cached) {
            $this->setCookieFromSession($cached['session_id']);
            $this->accountId = $cached['account_id'] ?? null;
            $this->authenticated = true;
        }

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? true,
            'http_errors' => false,
            'cookies' => $this->cookieJar,
            'headers' => [
                'Accept' => 'application/json, text/plain, */*',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
                'Origin' => $this->baseUrl,
                'Referer' => $this->baseUrl . '/',
            ],
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * eMikro Portal'a login olur.
     * POST /home/loginEmikro — multipart/form-data
     * Auth: PHPSESSID cookie ile session bazlı
     *
     * Login sonrası /status veya redirect'ten accountId alınır.
     * Tüm API çağrıları /cp/{accountId}/... altında yapılır.
     */
    public function login(): bool
    {
        try {
            $response = $this->http->post('/home/loginEmikro', [
                'json' => [
                    'email' => $this->email,
                    'password' => $this->password,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);

            if ($statusCode !== 200) {
                throw new AuthenticationException(
                    "Login failed with status {$statusCode}: {$rawBody}",
                    $statusCode,
                    $body ?? []
                );
            }

            // Response'da hata kontrolü
            if (is_array($body) && isset($body['error']) && $body['error']) {
                throw new AuthenticationException(
                    $body['message'] ?? $body['error'] ?? 'Login failed',
                    $statusCode,
                    $body
                );
            }

            // PHPSESSID cookie'yi yakala
            $sessionId = $this->extractSessionCookie();
            $this->authenticated = true;

            // accountId'yi al: login response'dan veya status endpoint'inden
            $this->resolveAccountId($body);

            // Session'ı cache'le
            if ($sessionId) {
                $this->sessionManager->save($sessionId, $this->accountId);
            }

            return true;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Login request failed: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * Login sonrası accountId'yi çöz.
     * 1. Login response'da varsa oradan al
     * 2. /home/GetAccounts endpoint'inden al (gerçek endpoint)
     * 3. Son çare: /accounts HTML'den parse et
     */
    private function resolveAccountId(?array $loginResponse): void
    {
        // 1. Login response'dan
        if ($loginResponse) {
            $this->accountId = $loginResponse['accountId']
                ?? $loginResponse['account_id']
                ?? $loginResponse['AccountId']
                ?? $loginResponse['id']
                ?? null;

            if ($this->accountId) {
                return;
            }
        }

        // 2. /home/GetAccounts endpoint'inden (gerçek çalışan endpoint)
        try {
            $accountsResponse = $this->http->get('/home/GetAccounts');
            if ($accountsResponse->getStatusCode() === 200) {
                $accountsBody = json_decode($accountsResponse->getBody()->getContents(), true);

                if (isset($accountsBody['accounts'][0]['id'])) {
                    $this->accountId = $accountsBody['accounts'][0]['id'];
                    return;
                }
            }
        } catch (\Throwable) {
            // GetAccounts başarısız, devam et
        }

        // 3. Son çare: /accounts HTML'den parse et
        try {
            $html = $this->http->get('/accounts')->getBody()->getContents();
            if (preg_match('#/cp/([a-f0-9\-]{36})/#i', $html, $matches)) {
                $this->accountId = $matches[1];
                return;
            }
        } catch (\Throwable) {
            // Son çare başarısız
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

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    /**
     * accountId'yi manuel set et (biliyorsan direkt verebilirsin)
     */
    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;
        return $this;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->accountId = null;
        $this->sessionManager->clear();
        $this->cookieJar = new CookieJar();
    }

    // ─── URL Builder ─────────────────────────────────────────────────

    /**
     * /cp/{accountId}/... path'ini oluşturur.
     * eMikro'nun tüm authenticated endpoint'leri bu pattern'i kullanır.
     */
    public function cpPath(string $path): string
    {
        if (!$this->accountId) {
            throw new AuthenticationException(
                'accountId not available. Login first or set it manually.',
                0,
                []
            );
        }

        return '/cp/' . $this->accountId . '/' . ltrim($path, '/');
    }

    // ─── HTTP Methods ────────────────────────────────────────────────

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * POST with multipart/form-data (eMikro birçok endpoint'te bunu kullanıyor)
     */
    public function postForm(string $endpoint, array $fields = []): array
    {
        $multipart = [];
        foreach ($fields as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => (string) $value];
        }

        return $this->request('POST', $endpoint, ['multipart' => $multipart]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, ['json' => $data]);
    }

    /**
     * Dosya upload (fatura XML vb.)
     */
    public function upload(string $endpoint, string $filePath, string $fieldName = 'file', array $extraFields = []): array
    {
        $multipart = [
            [
                'name' => $fieldName,
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        foreach ($extraFields as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => (string) $value];
        }

        return $this->request('POST', $endpoint, ['multipart' => $multipart]);
    }

    /**
     * Ham response döndürür (PDF/XML download vb.)
     */
    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();

        try {
            $response = $this->http->get($endpoint, ['query' => $query]);

            if (in_array($response->getStatusCode(), [401, 302])) {
                $this->authenticated = false;
                $this->login();
                $response = $this->http->get($endpoint, ['query' => $query]);
            }

            if ($response->getStatusCode() !== 200) {
                throw new ApiException(
                    'Download failed',
                    $response->getStatusCode(),
                    $endpoint
                );
            }

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Download failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    /**
     * Ham response objesi döndürür
     */
    public function raw(string $method, string $endpoint, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $this->ensureAuthenticated();
        return $this->http->request($method, $endpoint, $options);
    }

    // ─── Internal ────────────────────────────────────────────────────

    private function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureAuthenticated();

        try {
            $response = $this->http->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            // 401 veya login redirect → session expired
            if ($statusCode === 401 || ($statusCode === 302 && str_contains($response->getHeaderLine('Location'), 'login'))) {
                $this->authenticated = false;
                $this->login();

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            // HTML response = muhtemelen login sayfasına düşmüş
            $contentType = $response->getHeaderLine('Content-Type');
            if (str_contains($contentType, 'text/html') && str_contains($rawBody, 'loginEmikro')) {
                $this->authenticated = false;
                $this->login();

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            $body = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                throw new ApiException(
                    $body['message'] ?? $body['error'] ?? "API error: HTTP {$statusCode}",
                    $statusCode,
                    $endpoint,
                    $body ?? ['raw' => $rawBody]
                );
            }

            return $body ?? ['raw' => $rawBody];
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Request failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    private function extractSessionCookie(): ?string
    {
        foreach ($this->cookieJar->toArray() as $cookie) {
            if ($cookie['Name'] === 'PHPSESSID') {
                return $cookie['Value'];
            }
        }

        return null;
    }

    private function setCookieFromSession(string $sessionId): void
    {
        $domain = parse_url($this->baseUrl, PHP_URL_HOST);
        $this->cookieJar = CookieJar::fromArray(['PHPSESSID' => $sessionId], $domain);
    }

    // ─── Getters ─────────────────────────────────────────────────────

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getHttpClient(): GuzzleClient
    {
        return $this->http;
    }

    public function getCookieJar(): CookieJar
    {
        return $this->cookieJar;
    }
}
