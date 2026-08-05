<?php

namespace App\Interfaces;

interface ProductoRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?array;

    public function create(array $fields, ?string $id = null): array;

    public function update(string $id, array $fields): array;

    public function delete(string $id): void;
}
