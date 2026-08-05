<?php

namespace Tests\Support\Fakes;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\Carbon;

class InMemoryUserRepository implements UserRepositoryInterface, UsuarioRepositoryInterface
{
    public array $users = [];

    private int $sequence = 0;

    public function seed(string $id, string $name, string $email, string $hashedPassword): void
    {
        $this->users[$id] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'telefono' => null,
            'foto_url' => '',
            'perfiles' => [],
            'created_at' => Carbon::now(),
        ];
    }

    public function findByEmail(string $email): ?array
    {
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findById(string $id): ?array
    {
        return $this->users[$id] ?? null;
    }

    public function updatePassword(string $id, string $hashedPassword): void
    {
        if (isset($this->users[$id])) {
            $this->users[$id]['password'] = $hashedPassword;
        }
    }

    public function all(): array
    {
        return array_values($this->users);
    }

    public function any(): bool
    {
        return count($this->users) > 0;
    }

    public function find(string $id): ?array
    {
        return $this->users[$id] ?? null;
    }

    public function create(array $fields, ?string $id = null): array
    {
        $id ??= 'usuario-'.(++$this->sequence);
        $document = ['id' => $id, ...$fields];
        $this->users[$id] = $document;

        return $document;
    }

    public function update(string $id, array $fields): array
    {
        $this->users[$id] = [...($this->users[$id] ?? ['id' => $id]), ...$fields];

        return $this->users[$id];
    }

    public function delete(string $id): void
    {
        unset($this->users[$id]);
    }
}
