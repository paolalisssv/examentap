<?php

namespace App\Repositories\Firestore;

use App\Interfaces\ProductoRepositoryInterface;
use App\Services\Firebase\FirestoreRestClient;

class FirestoreProductoRepository implements ProductoRepositoryInterface
{
    private readonly string $collection;

    public function __construct(private readonly FirestoreRestClient $firestore)
    {
        $this->collection = (string) config('firebase.collections.productos');
    }

    public function all(): array
    {
        return $this->firestore->all($this->collection);
    }

    public function find(string $id): ?array
    {
        return $this->firestore->find($this->collection, $id);
    }

    public function create(array $fields, ?string $id = null): array
    {
        return $this->firestore->create($this->collection, $fields, $id);
    }

    public function update(string $id, array $fields): array
    {
        $this->firestore->update($this->collection, $id, $fields);

        return $this->firestore->find($this->collection, $id) ?? ['id' => $id, ...$fields];
    }

    public function delete(string $id): void
    {
        $this->firestore->delete($this->collection, $id);
    }
}
