<?php

namespace EdLugz\Tanda\Enums;

enum TandaStatus: string
{
    case SUCCESSFUL = 'S000000';
    case PROCESSING = 'P202000';
    case BAD_REQUEST = 'E400000';
    case UNAUTHORIZED = 'E401000';
    case FORBIDDEN = 'E403000';
    case NOT_FOUND = 'E404000';
    case DUPLICATE_RESOURCE = 'E409000';
    case PRODUCT_NOT_FOUND = 'E422005';
    case INSUFFICIENT_BALANCE = 'E422006';
    case PAYMENT_VALIDATION_FAILED = 'E422022';
    case SERVER_ERROR = 'E500000';
    case NOT_IMPLEMENTED = 'E501000';
    case SERVICE_UNAVAILABLE = 'E503000';

    public function isSuccessful(): bool
    {
        return $this === self::SUCCESSFUL;
    }

    public function isProcessing(): bool
    {
        return $this === self::PROCESSING;
    }

    public function isBadRequest(): bool
    {
        return $this === self::BAD_REQUEST;
    }
    public function isUnauthorized(): bool
    {
        return $this === self::UNAUTHORIZED;
    }
    public function isForbidden(): bool
    {
        return $this === self::FORBIDDEN;
    }
    public function isNotFound(): bool
    {
        return $this === self::NOT_FOUND;
    }
    public function isDuplicateResource(): bool
    {
        return $this === self::DUPLICATE_RESOURCE;
    }
    public function isProductNotFound(): bool
    {
        return $this === self::PRODUCT_NOT_FOUND;
    }
    public function isInsufficientBalance(): bool
    {
        return $this === self::INSUFFICIENT_BALANCE;
    }
    public function isPaymentValidationFailed(): bool
    {
        return $this === self::PAYMENT_VALIDATION_FAILED;
    }
    public function isServerError(): bool
    {
        return $this === self::SERVER_ERROR;
    }
    public function isNotImplemented(): bool
    {
        return $this === self::NOT_IMPLEMENTED;
    }
    public function isServiceUnavailable(): bool
    {
        return $this === self::SERVICE_UNAVAILABLE;
    }


    public function description(): string
    {
        return match ($this) {
            self::SUCCESSFUL => 'Successfully processed.',
            self::PROCESSING => 'Request has been received and is currently being processed.',
            self::BAD_REQUEST => 'Bad request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Access denied',
            self::NOT_FOUND => 'Not found',
            self::DUPLICATE_RESOURCE => 'Duplicate resource found',
            self::PRODUCT_NOT_FOUND => 'Request failed. Product not found',
            self::INSUFFICIENT_BALANCE => 'Request failed. Insufficient Wallet balance',
            self::PAYMENT_VALIDATION_FAILED => 'Payment Request Validation Failed',
            self::SERVER_ERROR => 'Internal Server Error',
            self::NOT_IMPLEMENTED => 'Not implemented',
            self::SERVICE_UNAVAILABLE => 'Service unavailable. Product / service is disabled or unavailable',
        };
    }
}