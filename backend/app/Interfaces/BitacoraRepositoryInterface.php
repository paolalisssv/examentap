<?php

namespace App\Interfaces;

interface BitacoraRepositoryInterface
{
    public function create(array $fields): array;
}
