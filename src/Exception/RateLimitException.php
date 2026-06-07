<?php

namespace Zinad\Crowdstrike\Exception;

class RateLimitException extends ApiException
{
    public function __construct(
        string $message,
        private readonly int $retryAfter = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, [], $previous);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
