<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?array;

    public function findById(string $id): ?array;

    public function updatePassword(string $id, string $hashedPassword): void;
}
