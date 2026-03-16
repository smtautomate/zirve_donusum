<?php

namespace ZirveDonusum;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Auth\SessionManager;
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

        // Eğer cache'de geçerli bir session varsa, cookie jar'a ekle
        $cachedSession = $this->sessionManager->getSessionId();
        if ($cachedSession) {
            $this->setCookieFromSession($cachedSession);
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
                'User-Agent' => 'ZirveDonusum-PHP-Client/1.0',
                'Origin' => $this->baseUrl,
                'Referer' => $this->baseUrl . '/',
            ],
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * eMikro Portal'a login olur.
     * POST /home/loginEmikro — multipart/form-data
     * Response: JSON (16 byte, muhtemelen {"success":true} benzeri)
     * Auth: PHPSESSID cookie ile session bazlı
     */
    public function login(): bool
    {
        try {
            $response = $this->http->post('/home/loginEmikro', [
                'multipart' => [
                    [
                        'name' => 'email',
                        'contents' => $this->email,
                    ],
                    [
                        'name' => 'password',
                        'contents' => $this->password,
                    ],
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

            // PHPSESSID cookie'yi yakala
            $sessionId = $this->extractSessionCookie();

            if ($sessionId) {
                $this->sessionManager->setSession($sessionId);
            }

            $this->authenticated = true;

            // Response'da hata mesajı varsa kontrol et
            if (is_array($body) && isset($body['error']) && $body['error']) {
                $this->authenticated = false;
                throw new AuthenticationException(
                    $body['message'] ?? $body['error'] ?? 'Login failed',
                    $statusCode,
                    $body
                );
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

    public function logout(): void
    {
        $this->authenticated = false;
        $this->sessionManager->clear();
        $this->cookieJar = new CookieJar();
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
            $response = $this->http->get($endpoint, [
                'query' => $query,
            ]);

            if ($response->getStatusCode() === 401 || $response->getStatusCode() === 302) {
                // Session expired, yeniden login
                $this->authenticated = false;
                $this->login();

                $response = $this->http->get($endpoint, [
                    'query' => $query,
                ]);
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
     * Ham response objesi döndürür (headers, status code vb. lazım olduğunda)
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

            // 401 veya login sayfasına redirect → session expired
            if ($statusCode === 401 || ($statusCode === 302 && str_contains($response->getHeaderLine('Location'), 'login'))) {
                $this->authenticated = false;
                $this->login();

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            // HTML response gelirse muhtemelen login sayfasına düşmüş
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

        $this->cookieJar = CookieJar::fromArray(
            ['PHPSESSID' => $sessionId],
            $domain
        );
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
