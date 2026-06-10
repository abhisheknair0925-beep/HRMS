<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected array $errors;

    public function __construct(string $message = "Business logic error occurred", int $code = 422, array $errors = [])
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
