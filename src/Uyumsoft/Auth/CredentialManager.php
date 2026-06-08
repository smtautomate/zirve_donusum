<?php

namespace ZirveDonusum\Uyumsoft\Auth;

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

    public function asUserInfo(): array
    {
        return [
            'Username' => $this->username,
            'Password' => $this->password,
        ];
    }

    public function isComplete(): bool
    {
        return $this->username !== '' && $this->password !== '';
    }
}
