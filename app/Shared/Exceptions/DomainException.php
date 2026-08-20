<?php

namespace App\Shared\Exceptions;

use Exception;

/**
 * Base class for business-rule violations raised from inside a domain's
 * Actions/Services. Caught centrally in bootstrap/app.php's exception
 * handler and rendered via ApiResponse.
 */
class DomainException extends Exception
{
    public function __construct(
        string $message,
        protected int $status = 422,
        protected string $errorCode = 'domain_error'
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
