<?php

namespace ZirveDonusum\Zirve;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Zirve\Auth\TokenManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

class HttpClient
{
    private GuzzleClient $http;
    private TokenManager $tokenManager;
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $timeout;
    private bool $authenticated = false;
    private ?string $accessToken = null;
    private ?int $parentCustomerId = null;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://yeniportal.zirvedonusum.com/accounting/api', '/');
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/zirve-portal';
        $this->tokenManager = new TokenManager(
            ($config['cache_token'] ?? true) ? $cacheDir : null
        );

        // Cache'den token yükle
        $cached = $this->tokenManager->load();
        if ($cached) {
            $this->accessToken = $cached['access_token'];
            $this->parentCustomerId = $cached['parent_customer_id'] ?? null;
            $this->authenticated = true;
        }

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? true,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * Zirve Portal'a login olur.
     * POST /auth/signin — JSON body {"username":"...","password":"..."}
     * Auth: JWT Bearer token bazlı
     *
     * Response: {"parentCustomerId":4508,"token":{"accessToken":"eyJ...","tokenType":"Bearer"}}
     */
    public function login(): bool
    {
        try {
            $response = $this->http->post('/auth/signin', [
                'json' => [
                    'username' => $this->username,
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

            // Token'ı çıkar
            $token = $body['token']['accessToken'] ?? null;

            if (!$token) {
                throw new AuthenticationException(
                    'Login response does not contain accessToken',
                    $statusCode,
                    $body ?? []
                );
            }

            $this->accessToken = $token;
            $this->parentCustomerId = $body['parentCustomerId'] ?? null;
            $this->authenticated = true;

            // Token'ı cache'le (23 saat TTL — token 24 saat geçerli)
            $this->tokenManager->save($this->accessToken, $this->parentCustomerId);

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

    public function getParentCustomerId(): ?int
    {
        return $this->parentCustomerId;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->accessToken = null;
        $this->parentCustomerId = null;
        $this->tokenManager->clear();
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

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, ['json' => $data]);
    }

    /**
     * Ham response döndürür (PDF/XML download vb.)
     */
    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();

        try {
            $options = array_merge(['query' => $query], ['headers' => $this->authHeaders()]);
            $response = $this->http->get($endpoint, $options);

            // 401/403 → token expired, yeniden login
            if (in_array($response->getStatusCode(), [401, 403])) {
                $this->clearAndRelogin();
                $options['headers'] = $this->authHeaders();
                $response = $this->http->get($endpoint, $options);
            }

            if ($response->getStatusCode() >= 400) {
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

        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }
        $options['headers'] = array_merge($options['headers'], $this->authHeaders());

        return $this->http->request($method, $endpoint, $options);
    }

    // ─── Internal ────────────────────────────────────────────────────

    /**
     * Merkezi request metodu.
     * 1. ensureAuthenticated() — gerekirse login olur
     * 2. Auth header'ları ekleyerek istek atar
     * 3. 401/403'te token'ı temizle, yeniden login, bir kez daha dene
     * 4. JSON decode
     * 5. 4xx/5xx'te ApiException fırlat
     * 6. Boş body'de boş array döndür
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureAuthenticated();

        try {
            // Auth header ekle
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers'] = array_merge($options['headers'], $this->authHeaders());

            $response = $this->http->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            // 401/403 → token expired, yeniden login ve tekrar dene
            if (in_array($statusCode, [401, 403])) {
                $this->clearAndRelogin();
                $options['headers'] = array_merge($options['headers'], $this->authHeaders());

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            // Boş body kontrolü (bazı endpoint'ler success'te boş döner)
            if (trim($rawBody) === '') {
                if ($statusCode >= 400) {
                    throw new ApiException(
                        "API error: HTTP {$statusCode}",
                        $statusCode,
                        $endpoint
                    );
                }
                return [];
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

            return $body ?? [];
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

    /**
     * Token'ı temizle ve yeniden login ol.
     */
    private function clearAndRelogin(): void
    {
        $this->authenticated = false;
        $this->accessToken = null;
        $this->tokenManager->clear();
        $this->login();
    }

    /**
     * Authorization header'ı oluştur.
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    // ─── Getters ─────────────────────────────────────────────────────

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }
}
