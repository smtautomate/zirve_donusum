<?php

namespace ZirveDonusum\Uyumsoft;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ZirveDonusum\Uyumsoft\Auth\CredentialManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

/**
 * Uyumsoft Action-Based API HttpClient.
 *
 * Tum istekler tek endpoint'e POST edilir:
 *   {"Action": "ActionName", "parameters": {..., "userInfo": {"Username": "...", "Password": "..."}}}
 *
 * E-Fatura/E-Arsiv: /api/BasicIntegrationApi
 * E-Irsaliye:       /api/DespatchApi
 * E-Adisyon:        /api/AdisyonApi
 * E-Bilet:          /api/BiletApi
 */
class HttpClient
{
    private GuzzleClient $guzzle;
    private CredentialManager $credentials;
    private string $endpointUrl;
    private bool $testMode;

    public function __construct(array $config, string $apiPath = 'BasicIntegrationApi')
    {
        $this->testMode = (bool) ($config['test_mode'] ?? true);

        $domain = rtrim(
            $config['base_url'] ?? ($this->testMode
                ? 'http://efatura-test.uyumsoft.com.tr'
                : 'http://efatura.uyumsoft.com.tr'),
            '/'
        );

        $this->endpointUrl = "{$domain}/api/{$apiPath}";

        $this->credentials = new CredentialManager(
            $config['username'] ?? '',
            $config['password'] ?? ''
        );

        $this->guzzle = new GuzzleClient([
            'timeout'     => $config['timeout'] ?? 30,
            'verify'      => $config['verify_ssl'] ?? true,
            'http_errors' => false,
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getEndpointUrl(): string
    {
        return $this->endpointUrl;
    }

    /**
     * Uyumsoft API aksiyonu calistir.
     *
     * @param  string $action     Aksiyon adi (orn. "SendInvoice", "IsEInvoiceUser")
     * @param  array  $parameters Aksiyon parametreleri (userInfo otomatik eklenir)
     * @return array
     */
    public function action(string $action, array $parameters = []): array
    {
        if (!$this->credentials->isComplete()) {
            throw new AuthenticationException(
                'Uyumsoft credentials eksik (username/password tanimlanmamis).'
            );
        }

        $body = [
            'Action'     => $action,
            'parameters' => array_merge($parameters, [
                'userInfo' => $this->credentials->asUserInfo(),
            ]),
        ];

        try {
            $response   = $this->guzzle->post($this->endpointUrl, ['json' => $body]);
            $statusCode = $response->getStatusCode();
            $rawBody    = $response->getBody()->getContents();

            if (in_array($statusCode, [401, 403])) {
                throw new AuthenticationException(
                    "Uyumsoft kimlik dogrulama reddedildi (HTTP {$statusCode}): {$rawBody}",
                    $statusCode
                );
            }

            if (trim($rawBody) === '') {
                if ($statusCode >= 400) {
                    throw new ApiException(
                        "Uyumsoft API hatasi: HTTP {$statusCode}",
                        $statusCode,
                        $action
                    );
                }
                return [];
            }

            $parsed = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                $msg = $parsed['Message']
                    ?? $parsed['message']
                    ?? $parsed['Error']
                    ?? $parsed['ErrorMessage']
                    ?? "Uyumsoft API hatasi: HTTP {$statusCode}";
                throw new ApiException($msg, $statusCode, $action, $parsed ?? ['raw' => $rawBody]);
            }

            // Bazi Uyumsoft endpoint'leri IsSucceded:false ile HTTP 200 doner
            if (is_array($parsed) && isset($parsed['IsSucceded']) && $parsed['IsSucceded'] === false) {
                throw new ApiException(
                    $parsed['Message'] ?? 'Uyumsoft API: IsSucceded=false',
                    $statusCode,
                    $action,
                    $parsed
                );
            }

            return $parsed ?? [];
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Uyumsoft istek basarisiz ({$action}): {$e->getMessage()}",
                $e->getCode(),
                $action,
                [],
                $e
            );
        }
    }
}
