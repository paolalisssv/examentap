<?php

namespace App\Repositories\Firestore;

use App\Interfaces\TokenRepositoryInterface;
use App\Services\Firebase\FirestoreRestClient;
use Illuminate\Support\Carbon;

class FirestoreTokenRepository implements TokenRepositoryInterface
{
    private readonly string $collection;

    public function __construct(private readonly FirestoreRestClient $firestore)
    {
        $this->collection = (string) config('firebase.collections.auth_tokens');
    }

    public function create(string $userId, string $tokenHash, Carbon $expiresAt): void
    {
        // El hash del token se usa como ID del documento, permitiendo buscarlo
        // directamente por ID en vez de hacer una query por campo.
        $this->firestore->create($this->collection, [
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now(),
        ], $tokenHash);
    }

    public function findValidByHash(string $tokenHash): ?array
    {
        $token = $this->firestore->find($this->collection, $tokenHash);

        if ($token === null) {
            return null;
        }

        // Firestore no expira documentos automáticamente: la vigencia se valida
        // aquí comparando expires_at contra la hora actual.
        if (! $token['expires_at'] instanceof Carbon || $token['expires_at']->isPast()) {
            return null;
        }

        return $token;
    }

    public function delete(string $tokenHash): void
    {
        $this->firestore->delete($this->collection, $tokenHash);
    }
}
