<?php

namespace ZirveDonusum\Parasut\Auth;

/**
 * Parasut OAuth2 access + refresh token yonetimi.
 * Access token tipik 7200s (2 saat); refresh ile expire'a yakin yenilenir.
 */
class TokenManager
{
    private ?string $accessToken = null;
    private ?string $refreshToken = null;
    private ?int $expiresAt = null;
    private ?string $cacheFile = null;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $this->cacheFile = rtrim($cacheDir, '/') . '/parasut_token.json';
            $this->loadFromFile();
        }
    }

    public function save(string $accessToken, ?string $refreshToken = null, int $ttl = 7200): void
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    public function load(): ?array
    {
        if ($this->accessToken && $this->expiresAt && time() < ($this->expiresAt - 60)) {
            return [
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'expires_at' => $this->expiresAt,
            ];
        }

        return null;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function isValid(): bool
    {
        return $this->load() !== null;
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiresAt !== null && time() >= ($this->expiresAt - 60);
    }

    public function clear(): void
    {
        $this->accessToken = null;
        $this->refreshToken = null;
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
            'refresh_token' => $this->refreshToken,
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
            $this->refreshToken = $data['refresh_token'] ?? null;
            $this->expiresAt = $data['expires_at'];
        }
    }
}
