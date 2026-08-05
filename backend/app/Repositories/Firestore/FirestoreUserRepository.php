<?php

namespace App\Repositories\Firestore;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use App\Services\Firebase\FirestoreRestClient;
use Illuminate\Support\Carbon;

class FirestoreUserRepository implements UserRepositoryInterface, UsuarioRepositoryInterface
{
    private readonly string $collection;

    public function __construct(private readonly FirestoreRestClient $firestore)
    {
        $this->collection = (string) config('firebase.collections.users');
    }

    public function findByEmail(string $email): ?array
    {
        return $this->firestore->findOneByField($this->collection, 'email', $email);
    }

    public function findById(string $id): ?array
    {
        return $this->firestore->find($this->collection, $id);
    }

    public function updatePassword(string $id, string $hashedPassword): void
    {
        $this->firestore->update($this->collection, $id, [
            'password' => $hashedPassword,
            'updated_at' => Carbon::now(),
        ]);
    }

    public function all(): array
    {
        return $this->firestore->all($this->collection);
    }

    public function any(): bool
    {
        return $this->firestore->any($this->collection);
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
