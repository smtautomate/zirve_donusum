<?php

namespace ZirveDonusum\Auth;

/**
 * PHPSESSID cookie + accountId yönetimi.
 * eMikro session-based auth kullanıyor ve tüm API çağrıları /cp/{accountId}/ altında.
 */
class SessionManager
{
    private ?string $sessionId = null;
    private ?string $accountId = null;
    private ?int $expiresAt = null;
    private ?string $cacheFile = null;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $this->cacheFile = rtrim($cacheDir, '/') . '/emikro_session.json';
            $this->loadFromFile();
        }
    }

    public function save(string $sessionId, ?string $accountId = null, int $ttl = 82800): void
    {
        $this->sessionId = $sessionId;
        $this->accountId = $accountId;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    /**
     * Session + accountId'yi döndür (geçerliyse)
     */
    public function load(): ?array
    {
        if ($this->sessionId && $this->expiresAt && time() < $this->expiresAt) {
            return [
                'session_id' => $this->sessionId,
                'account_id' => $this->accountId,
            ];
        }

        $this->sessionId = null;
        $this->accountId = null;
        $this->expiresAt = null;
        return null;
    }

    public function getSessionId(): ?string
    {
        $data = $this->load();
        return $data['session_id'] ?? null;
    }

    public function getAccountId(): ?string
    {
        $data = $this->load();
        return $data['account_id'] ?? null;
    }

    public function isValid(): bool
    {
        return $this->load() !== null;
    }

    public function clear(): void
    {
        $this->sessionId = null;
        $this->accountId = null;
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
            'session_id' => $this->sessionId,
            'account_id' => $this->accountId,
            'expires_at' => $this->expiresAt,
        ]));
    }

    private function loadFromFile(): void
    {
        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
            return;
        }

        $data = json_decode(file_get_contents($this->cacheFile), true);

        if ($data && isset($data['session_id'], $data['expires_at'])) {
            $this->sessionId = $data['session_id'];
            $this->accountId = $data['account_id'] ?? null;
            $this->expiresAt = $data['expires_at'];
        }
    }
}
