<?php

namespace ZirveDonusum\Zirve\Auth;

/**
 * JWT access token yönetimi.
 * Zirve Portal JWT-based auth kullanıyor — session/cookie yok.
 * Token 24 saat geçerli, cache TTL 23 saat (güvenlik marjı).
 */
class TokenManager
{
    private ?string $accessToken = null;
    private ?int $parentCustomerId = null;
    private ?int $expiresAt = null;
    private ?string $cacheFile = null;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $this->cacheFile = rtrim($cacheDir, '/') . '/zirve_token.json';
            $this->loadFromFile();
        }
    }

    public function save(string $accessToken, ?int $parentCustomerId = null, int $ttl = 82800): void
    {
        $this->accessToken = $accessToken;
        $this->parentCustomerId = $parentCustomerId;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    /**
     * Token + parentCustomerId'yi döndür (geçerliyse)
     */
    public function load(): ?array
    {
        if ($this->accessToken && $this->expiresAt && time() < $this->expiresAt) {
            return [
                'access_token' => $this->accessToken,
                'parent_customer_id' => $this->parentCustomerId,
            ];
        }

        $this->accessToken = null;
        $this->parentCustomerId = null;
        $this->expiresAt = null;
        return null;
    }

    public function isValid(): bool
    {
        return $this->load() !== null;
    }

    public function clear(): void
    {
        $this->accessToken = null;
        $this->parentCustomerId = null;
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
            'parent_customer_id' => $this->parentCustomerId,
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
            $this->parentCustomerId = $data['parent_customer_id'] ?? null;
            $this->expiresAt = $data['expires_at'];
        }
    }
}
