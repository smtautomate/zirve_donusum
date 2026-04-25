<?php

namespace ZirveDonusum\Parasut;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Parasut\Auth\TokenManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

/**
 * Parasut V4 OAuth2 (password grant) HttpClient.
 * Base: https://api.parasut.com/v4
 * Auth: https://api.parasut.com/oauth/token
 *
 * Endpoint pattern: /{companyId}/{resource}
 * 401 alindiginda once refresh, basarisizsa yeniden password grant.
 */
class HttpClient
{
    private GuzzleClient $http;
    private GuzzleClient $authHttp;
    private TokenManager $tokenManager;
    private string $baseUrl;
    private string $authUrl;
    private string $username;
    private string $password;
    private string $companyId;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private int $timeout;
    private bool $authenticated = false;
    private ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.parasut.com/v4', '/');
        $this->authUrl = $config['auth_url'] ?? 'https://api.parasut.com/oauth/token';
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->companyId = (string)($config['company_id'] ?? '');
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->redirectUri = $config['redirect_uri'] ?? 'urn:ietf:wg:oauth:2.0:oob';
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/parasut';
        $this->tokenManager = new TokenManager(
            ($config['cache_token'] ?? true) ? $cacheDir : null
        );

        $cached = $this->tokenManager->load();
        if ($cached) {
            $this->accessToken = $cached['access_token'];
            $this->authenticated = true;
        }

        $verify = $config['verify_ssl'] ?? true;

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => $this->timeout,
            'verify' => $verify,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->authHttp = new GuzzleClient([
            'timeout' => $this->timeout,
            'verify' => $verify,
            'http_errors' => false,
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * OAuth2 password grant ile token alir.
     */
    public function login(): bool
    {
        try {
            $response = $this->authHttp->post($this->authUrl, [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $this->username,
                    'password' => $this->password,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            return $this->handleTokenResponse($response);
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Parasut login request failed: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * Refresh token ile access token yeniler.
     */
    public function refresh(): bool
    {
        $refreshToken = $this->tokenManager->getRefreshToken();
        if (!$refreshToken) {
            return $this->login();
        }

        try {
            $response = $this->authHttp->post($this->authUrl, [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $refreshToken,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return $this->handleTokenResponse($response);
            }

            // refresh basarisizsa fresh login
            return $this->login();
        } catch (GuzzleException) {
            return $this->login();
        }
    }

    private function handleTokenResponse($response): bool
    {
        $statusCode = $response->getStatusCode();
        $rawBody = $response->getBody()->getContents();
        $body = json_decode($rawBody, true);

        if ($statusCode !== 200 || !isset($body['access_token'])) {
            throw new AuthenticationException(
                "Parasut auth failed (HTTP {$statusCode}): {$rawBody}",
                $statusCode,
                $body ?? []
            );
        }

        $this->accessToken = $body['access_token'];
        $this->authenticated = true;
        $ttl = (int)($body['expires_in'] ?? 7200);
        $this->tokenManager->save(
            $body['access_token'],
            $body['refresh_token'] ?? null,
            $ttl
        );

        return true;
    }

    public function ensureAuthenticated(): void
    {
        if (!$this->authenticated) {
            $this->login();
            return;
        }

        if ($this->tokenManager->isExpiringSoon()) {
            $this->refresh();
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->accessToken = null;
        $this->tokenManager->clear();
    }

    // ─── HTTP Methods ────────────────────────────────────────────────

    /**
     * Sirket-scope endpoint cagirir: /{companyId}/{path}
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $this->prefix($endpoint), ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $this->prefix($endpoint), ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $this->prefix($endpoint), ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $this->prefix($endpoint), ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $this->prefix($endpoint), ['json' => $data]);
    }

    /**
     * Root endpoint (sirket prefix'siz). Ornegin /me veya / (api home).
     */
    public function getRoot(string $endpoint = '', array $query = []): array
    {
        $endpoint = ltrim($endpoint, '/');
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Ham response (PDF vb. binary download icin).
     */
    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();
        $endpoint = $this->prefix($endpoint);

        try {
            $options = ['query' => $query, 'headers' => $this->authHeaders()];
            $response = $this->http->get($endpoint, $options);

            if (in_array($response->getStatusCode(), [401, 403])) {
                $this->refresh();
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

    // ─── Internal ────────────────────────────────────────────────────

    private function prefix(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        if ($this->companyId === '') {
            return $endpoint;
        }
        return "{$this->companyId}/{$endpoint}";
    }

    private function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureAuthenticated();

        try {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers'] = array_merge($options['headers'], $this->authHeaders());

            $response = $this->http->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            if (in_array($statusCode, [401, 403])) {
                $this->refresh();
                $options['headers'] = array_merge($options['headers'], $this->authHeaders());

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            if (trim($rawBody) === '') {
                if ($statusCode >= 400) {
                    throw new ApiException(
                        "Parasut API error: HTTP {$statusCode}",
                        $statusCode,
                        $endpoint
                    );
                }
                return [];
            }

            $body = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                $msg = $body['errors'][0]['title']
                    ?? $body['errors'][0]['detail']
                    ?? $body['error_description']
                    ?? $body['message']
                    ?? "Parasut API error: HTTP {$statusCode}";
                throw new ApiException(
                    $msg,
                    $statusCode,
                    $endpoint,
                    $body ?? ['raw' => $rawBody]
                );
            }

            return $body ?? [];
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Parasut request failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }
}
