<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class TenantException extends Exception
{
    protected array $errors;

    public function __construct(string $message = "Tenant configuration error occurred", int $code = 400, array $errors = [])
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Retrieve error details associated with this exception.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
