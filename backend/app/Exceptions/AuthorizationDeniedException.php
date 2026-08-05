<?php

namespace App\Exceptions;

class AuthorizationDeniedException extends ApiException
{
    public function __construct(string $message = 'No tienes permisos para realizar esta acción.')
    {
        parent::__construct($message, 403);
    }
}
