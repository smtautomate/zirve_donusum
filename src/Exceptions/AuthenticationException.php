<?php

namespace ZirveDonusum\Exceptions;

class AuthenticationException extends ZirveException
{
    public function __construct(string $message = 'Authentication failed', int $code = 401, array $errorData = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $errorData, $previous);
    }
}
