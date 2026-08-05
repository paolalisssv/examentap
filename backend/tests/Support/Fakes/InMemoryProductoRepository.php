<?php

namespace Tests\Support\Fakes;

use App\Interfaces\ProductoRepositoryInterface;

class InMemoryProductoRepository implements ProductoRepositoryInterface
{
    public array $productos = [];

    private int $sequence = 0;

    public function all(): array
    {
        return array_values($this->productos);
    }

    public function find(string $id): ?array
    {
        return $this->productos[$id] ?? null;
    }

    public function create(array $fields, ?string $id = null): array
    {
        $id ??= 'producto-'.(++$this->sequence);
        $document = ['id' => $id, ...$fields];
        $this->productos[$id] = $document;

        return $document;
    }

    public function update(string $id, array $fields): array
    {
        $this->productos[$id] = [...($this->productos[$id] ?? ['id' => $id]), ...$fields];

        return $this->productos[$id];
    }

    public function delete(string $id): void
    {
        unset($this->productos[$id]);
    }
}
