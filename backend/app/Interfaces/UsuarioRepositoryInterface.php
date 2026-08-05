<?php

namespace App\Interfaces;

interface UsuarioRepositoryInterface
{
    public function all(): array;

    public function any(): bool;

    public function find(string $id): ?array;

    public function findByEmail(string $email): ?array;

    public function create(array $fields, ?string $id = null): array;

    public function update(string $id, array $fields): array;

    public function delete(string $id): void;
}
