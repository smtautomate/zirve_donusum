<?php

namespace ZirveDonusum\Gib\Auth;

/**
 * GİB e-Arşiv Portal session token yönetimi.
 * GİB form-encoded session token kullanıyor — JWT yok, cookie yok.
 * Token ~1 saat geçerli, cache TTL varsayılan 3600 saniye.
 */
class TokenManager
{
    private ?string $token = null;
    private ?int $expiresAt = null;
    private ?string $cacheFile = null;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $this->cacheFile = rtrim($cacheDir, '/') . '/gib_token.json';
            $this->loadFromFile();
        }
    }

    /**
     * Token'ı kaydet.
     *
     * @param string $token GİB session token
     * @param int $ttl Saniye cinsinden geçerlilik süresi (varsayılan 1 saat)
     */
    public function save(string $token, int $ttl = 3600): void
    {
        $this->token = $token;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    /**
     * Geçerli token'ı döndür, süresi dolmuşsa null.
     *
     * @return array{token: string, expires_at: int}|null
     */
    public function load(): ?array
    {
        if ($this->token && $this->expiresAt && time() < $this->expiresAt) {
            return [
                'token' => $this->token,
                'expires_at' => $this->expiresAt,
            ];
        }

        $this->token = null;
        $this->expiresAt = null;
        return null;
    }

    public function isValid(): bool
    {
        return $this->load() !== null;
    }

    public function clear(): void
    {
        $this->token = null;
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
            'token' => $this->token,
            'expires_at' => $this->expiresAt,
        ]));
    }

    private function loadFromFile(): void
    {
        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
            return;
        }

        $data = json_decode(file_get_contents($this->cacheFile), true);

        if ($data && isset($data['token'], $data['expires_at'])) {
            $this->token = $data['token'];
            $this->expiresAt = $data['expires_at'];
        }
    }
}
