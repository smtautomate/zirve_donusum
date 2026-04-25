<?php

namespace ZirveDonusum\Uyumsoft\Auth;

/**
 * Uyumsoft REST API stateless credential manager.
 * Token alma yerine her istekte UserName/Password JSON body veya header ile gonderilir.
 * Bu sinif sadece credential'lari tek noktada toplar.
 */
class CredentialManager
{
    public function __construct(
        private string $username,
        private string $password,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Uyumsoft her istekte UserInfo objesi bekler.
     */
    public function asUserInfo(): array
    {
        return [
            'UserName' => $this->username,
            'Password' => $this->password,
        ];
    }

    /**
     * HTTP Basic Authorization header value.
     */
    public function asBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode("{$this->username}:{$this->password}");
    }

    /**
     * Credential'larin set edildigini dogrular.
     */
    public function isComplete(): bool
    {
        return $this->username !== '' && $this->password !== '';
    }
}
