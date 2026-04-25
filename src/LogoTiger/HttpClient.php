<?php

namespace ZirveDonusum\LogoTiger;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\LogoTiger\Auth\TokenManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

/**
 * Logo Tiger REST API HttpClient.
 *
 * Auth: POST /token (form-encoded, OAuth2 password grant) -> access_token (Bearer).
 * Tum diger isteklerde Authorization: Bearer ... header'i ile.
 *
 * Logo REST API tipik olarak Logo Connect uzerinden lokal port'tan (32001) yayinlanir.
 * firmaNo / donemNo cogu endpoint'te query parametresi olarak gerekir;
 * BaseService bunlari otomatik olarak ekler.
 */
class HttpClient
{
    private GuzzleClient $http;
    private TokenManager $tokenManager;
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $clientId;
    private string $grantType;
    private string $firmaNo;
    private string $donemNo;
    private int $timeout;
    private bool $authenticated = false;
    private ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://localhost:32001/api/v1/', '/');
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->clientId = $config['client_id'] ?? 'logo';
        $this->grantType = $config['grant_type'] ?? 'password';
        $this->firmaNo = (string)($config['firma_no'] ?? '1');
        $this->donemNo = (string)($config['donem_no'] ?? '1');
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/logo-tiger';
        $this->tokenManager = new TokenManager(
            ($config['cache_token'] ?? true) ? $cacheDir : null
        );

        $cached = $this->tokenManager->load();
        if ($cached) {
            $this->accessToken = $cached['access_token'];
            $this->authenticated = true;
        }

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? false,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    // ─── Authentication ──────────────────────────────────────────────

    public function login(): bool
    {
        try {
            $response = $this->http->post('token', [
                'form_params' => [
                    'grant_type' => $this->grantType,
                    'client_id' => $this->clientId,
                    'username' => $this->username,
                    'password' => $this->password,
                    'firmano' => $this->firmaNo,
                    'donemno' => $this->donemNo,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);

            if ($statusCode !== 200 || !isset($body['access_token'])) {
                throw new AuthenticationException(
                    "Logo Tiger login failed (HTTP {$statusCode}): {$rawBody}",
                    $statusCode,
                    $body ?? []
                );
            }

            $this->accessToken = $body['access_token'];
            $this->authenticated = true;
            $ttl = (int)($body['expires_in'] ?? 1800);
            $this->tokenManager->save($body['access_token'], $ttl);

            return true;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Logo Tiger login request failed: ' . $e->getMessage(),
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

    public function getFirmaNo(): string
    {
        return $this->firmaNo;
    }

    public function getDonemNo(): string
    {
        return $this->donemNo;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->accessToken = null;
        $this->tokenManager->clear();
    }

    // ─── HTTP Methods ────────────────────────────────────────────────

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $this->withFirmaDonem($query)]);
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

    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();

        try {
            $options = [
                'query' => $this->withFirmaDonem($query),
                'headers' => $this->authHeaders(),
            ];
            $response = $this->http->get($endpoint, $options);

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

    // ─── Internal ────────────────────────────────────────────────────

    /**
     * Query parametrelerine firmaNo / donemNo ekler (override yapilmadiysa).
     */
    private function withFirmaDonem(array $query): array
    {
        if (!isset($query['firmaNo']) && !isset($query['firma_no'])) {
            $query['firmaNo'] = $this->firmaNo;
        }
        if (!isset($query['donemNo']) && !isset($query['donem_no'])) {
            $query['donemNo'] = $this->donemNo;
        }
        return $query;
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
                $this->clearAndRelogin();
                $options['headers'] = array_merge($options['headers'], $this->authHeaders());

                $response = $this->http->request($method, $endpoint, $options);
                $statusCode = $response->getStatusCode();
                $rawBody = $response->getBody()->getContents();
            }

            if (trim($rawBody) === '') {
                if ($statusCode >= 400) {
                    throw new ApiException(
                        "Logo Tiger API error: HTTP {$statusCode}",
                        $statusCode,
                        $endpoint
                    );
                }
                return [];
            }

            $body = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                $msg = $body['message']
                    ?? $body['error_description']
                    ?? $body['error']
                    ?? "Logo Tiger API error: HTTP {$statusCode}";
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
                "Logo Tiger request failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    private function clearAndRelogin(): void
    {
        $this->authenticated = false;
        $this->accessToken = null;
        $this->tokenManager->clear();
        $this->login();
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
