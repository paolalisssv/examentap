<?php

namespace App\Exceptions;

class DomainException extends ApiException
{
    public function __construct(string $message = '', mixed $errors = null)
    {
        parent::__construct($message, 422, $errors);
    }
}
