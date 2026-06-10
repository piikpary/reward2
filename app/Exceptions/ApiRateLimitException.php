<?php

namespace App\Exceptions;

use Exception;

class ApiRateLimitException extends Exception
{
    protected int $retryAfterSeconds;

    public function __construct(string $message, int $retryAfterSeconds, int $code = 429)
    {
        parent::__construct($message, $code);

        $this->retryAfterSeconds = $retryAfterSeconds;
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }

    public function retryAfterMinutes(): int
    {
        return (int) ceil($this->retryAfterSeconds / 60);
    }
}