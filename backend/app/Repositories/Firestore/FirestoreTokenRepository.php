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
