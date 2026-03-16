<?php

namespace ZirveDonusum\Exceptions;

class ApiException extends ZirveException
{
    protected ?string $endpoint;

    public function __construct(string $message = 'API request failed', int $code = 0, ?string $endpoint = null, array $errorData = [], ?\Throwable $previous = null)
    {
        $this->endpoint = $endpoint;
        parent::__construct($message, $code, $errorData, $previous);
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }
}
