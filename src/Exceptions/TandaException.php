<?php

namespace EdLugz\Tanda\Exceptions;

use Exception;

class TandaException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly array $errorDetails = []
    ) {
        parent::__construct($message, $statusCode);
    }

    public function __toString(): string
    {
        return sprintf(
            "[TandaRequestException] %s (Status Code: %d) Details: %s",
            $this->message,
            $this->statusCode,
            json_encode($this->errorDetails, JSON_PRETTY_PRINT)
        );
    }
}
