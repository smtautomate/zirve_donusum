<?php

namespace ZirveDonusum\Auth;

/**
 * PHPSESSID cookie yönetimi.
 * eMikro session-based auth kullanıyor, Bearer token değil.
 */
class SessionManager
{
    private ?string $sessionId = null;
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

    public function setSession(string $sessionId, int $ttl = 82800): void
    {
        $this->sessionId = $sessionId;
        $this->expiresAt = time() + $ttl;
        $this->saveToFile();
    }

    public function getSessionId(): ?string
    {
        if ($this->sessionId && $this->expiresAt && time() < $this->expiresAt) {
            return $this->sessionId;
        }

        $this->sessionId = null;
        $this->expiresAt = null;
        return null;
    }

    public function isValid(): bool
    {
        return $this->getSessionId() !== null;
    }

    public function clear(): void
    {
        $this->sessionId = null;
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
            $this->expiresAt = $data['expires_at'];
        }
    }
}
