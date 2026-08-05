<?php

namespace App\Exceptions;

class InvalidCredentialsException extends ApiException
{
    public function __construct(string $message = 'Las credenciales proporcionadas son incorrectas.')
    {
        parent::__construct($message, 401);
    }
}
