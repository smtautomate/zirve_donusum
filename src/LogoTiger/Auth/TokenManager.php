<?php

namespace ZirveDonusum\LogoTiger\Auth;

/**
 * Logo REST API Bearer token yonetimi.
 * Logo Connect / Logo REST tipik 1800s (30 dakika) token verir.
 */
class TokenManager
{
    private ?string $accessToken = null;
    private ?int $expiresAt = null;
    private ?string $cacheFile = null;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $this->cacheFile = rtrim($cacheDir, '/') . '/logo_tiger_token.json';
            $this->loadFromFile();
        }
    }

    public function save(string $accessToken, int $ttl = 1800): void
    {
        $this->accessToken = $accessToken;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    public function load(): ?array
    {
        if ($this->accessToken && $this->expiresAt && time() < ($this->expiresAt - 30)) {
            return [
                'access_token' => $this->accessToken,
                'expires_at' => $this->expiresAt,
            ];
        }

        return null;
    }

    public function isValid(): bool
    {
        return $this->load() !== null;
    }

    public function clear(): void
    {
        $this->accessToken = null;
        $this->expiresAt = null;

        if ($this->cacheFile && file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    private function saveToFile(): void
    {
        if (!$this->cacheFile) {
            return;
        }

        file_put_contents($this->cacheFile, json_encode([
            'access_token' => $this->accessToken,
            'expires_at' => $this->expiresAt,
        ]));
    }

    private function loadFromFile(): void
    {
        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
            return;
        }

        $data = json_decode(file_get_contents($this->cacheFile), true);

        if ($data && isset($data['access_token'], $data['expires_at'])) {
            $this->accessToken = $data['access_token'];
            $this->expiresAt = $data['expires_at'];
        }
    }
}
