<?php

namespace App\Repositories\Firestore;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Services\Firebase\FirestoreRestClient;

class FirestoreBitacoraRepository implements BitacoraRepositoryInterface
{
    private readonly string $collection;

    public function __construct(private readonly FirestoreRestClient $firestore)
    {
        $this->collection = (string) config('firebase.collections.bitacora');
    }

    public function create(array $fields): array
    {
        return $this->firestore->create($this->collection, $fields);
    }
}
