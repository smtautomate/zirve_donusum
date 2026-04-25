<?php

namespace ZirveDonusum\Uyumsoft;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Uyumsoft\Auth\CredentialManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

/**
 * Uyumsoft REST API HttpClient.
 *
 * Stateless: her istekte Basic Auth header + JSON body icinde UserInfo gonderir.
 * Test:  https://efatura-test.uyumsoft.com.tr
 * Prod:  https://efatura.uyumsoft.com.tr
 */
class HttpClient
{
    private GuzzleClient $http;
    private CredentialManager $credentials;
    private string $baseUrl;
    private bool $testMode;
    private int $timeout;

    public function __construct(array $config)
    {
        $this->testMode = (bool)($config['test_mode'] ?? true);
        $this->baseUrl = rtrim(
            $config['base_url'] ?? ($this->testMode
                ? 'https://efatura-test.uyumsoft.com.tr/services'
                : 'https://efatura.uyumsoft.com.tr/services'),
            '/'
        );
        $this->timeout = $config['timeout'] ?? 30;

        $this->credentials = new CredentialManager(
            $config['username'] ?? '',
            $config['password'] ?? ''
        );

        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? true,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function isAuthenticated(): bool
    {
        return $this->credentials->isComplete();
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function ensureAuthenticated(): void
    {
        if (!$this->credentials->isComplete()) {
            throw new AuthenticationException(
                'Uyumsoft credentials are not configured (username/password missing).'
            );
        }
    }

    // ─── HTTP Methods ────────────────────────────────────────────────

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $payload = array_merge(['UserInfo' => $this->credentials->asUserInfo()], $data);
        return $this->request('POST', $endpoint, ['json' => $payload]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        $payload = array_merge(['UserInfo' => $this->credentials->asUserInfo()], $data);
        return $this->request('PUT', $endpoint, ['json' => $payload]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        $payload = array_merge(['UserInfo' => $this->credentials->asUserInfo()], $data);
        return $this->request('DELETE', $endpoint, ['json' => $payload]);
    }

    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();

        try {
            $response = $this->http->get($endpoint, [
                'query' => $query,
                'headers' => $this->authHeaders(),
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new ApiException(
                    'Uyumsoft download failed',
                    $response->getStatusCode(),
                    $endpoint
                );
            }

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Uyumsoft download failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    // ─── Internal ────────────────────────────────────────────────────

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
                throw new AuthenticationException(
                    'Uyumsoft authentication rejected (HTTP ' . $statusCode . '): ' . $rawBody,
                    $statusCode
                );
            }

            if (trim($rawBody) === '') {
                if ($statusCode >= 400) {
                    throw new ApiException(
                        "Uyumsoft API error: HTTP {$statusCode}",
                        $statusCode,
                        $endpoint
                    );
                }
                return [];
            }

            $body = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                $msg = $body['Message']
                    ?? $body['message']
                    ?? $body['Error']
                    ?? $body['ErrorMessage']
                    ?? "Uyumsoft API error: HTTP {$statusCode}";
                throw new ApiException(
                    $msg,
                    $statusCode,
                    $endpoint,
                    $body ?? ['raw' => $rawBody]
                );
            }

            // Bazi Uyumsoft endpoint'leri IsSucceded:false ile 200 doner
            if (is_array($body) && isset($body['IsSucceded']) && $body['IsSucceded'] === false) {
                throw new ApiException(
                    $body['Message'] ?? 'Uyumsoft API returned IsSucceded=false',
                    $statusCode,
                    $endpoint,
                    $body
                );
            }

            return $body ?? [];
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Uyumsoft request failed: {$e->getMessage()}",
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
            'Authorization' => $this->credentials->asBasicAuthHeader(),
        ];
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getCredentials(): CredentialManager
    {
        return $this->credentials;
    }
}
