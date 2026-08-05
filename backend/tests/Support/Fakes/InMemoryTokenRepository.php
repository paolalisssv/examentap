<?php

namespace Tests\Support\Fakes;

use App\Interfaces\TokenRepositoryInterface;
use Illuminate\Support\Carbon;

class InMemoryTokenRepository implements TokenRepositoryInterface
{
    public array $tokens = [];

    public function create(string $userId, string $tokenHash, Carbon $expiresAt): void
    {
        $this->tokens[$tokenHash] = [
            'user_id' => $userId,
            'expires_at' => $expiresAt,
        ];
    }

    public function findValidByHash(string $tokenHash): ?array
    {
        $token = $this->tokens[$tokenHash] ?? null;

        if ($token === null || $token['expires_at']->isPast()) {
            return null;
        }

        return $token;
    }

    public function delete(string $tokenHash): void
    {
        unset($this->tokens[$tokenHash]);
    }
}
