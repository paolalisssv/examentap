<?php

namespace App\Services\System;

use App\Interfaces\UsuarioRepositoryInterface;

class SystemService
{
    public function __construct(private readonly UsuarioRepositoryInterface $usuarios)
    {
    }

    public function isInitialized(): bool
    {
        return $this->usuarios->any();
    }
}
