<?php

namespace App\Interfaces;

use Illuminate\Support\Carbon;

interface TokenRepositoryInterface
{
    public function create(string $userId, string $tokenHash, Carbon $expiresAt): void;

    public function findValidByHash(string $tokenHash): ?array;

    public function delete(string $tokenHash): void;
}
