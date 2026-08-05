<?php

namespace Tests\Support\Fakes;

use App\Interfaces\PerfilRepositoryInterface;

class InMemoryPerfilRepository implements PerfilRepositoryInterface
{
    public array $perfiles = [];

    private int $sequence = 0;

    public function all(): array
    {
        return array_values($this->perfiles);
    }

    public function find(string $id): ?array
    {
        return $this->perfiles[$id] ?? null;
    }

    public function findByNombre(string $nombre): ?array
    {
        foreach ($this->perfiles as $perfil) {
            if ($perfil['name'] === $nombre) {
                return $perfil;
            }
        }

        return null;
    }

    public function create(array $fields, ?string $id = null): array
    {
        $id ??= 'perfil-'.(++$this->sequence);
        $document = ['id' => $id, ...$fields];
        $this->perfiles[$id] = $document;

        return $document;
    }

    public function update(string $id, array $fields): array
    {
        $this->perfiles[$id] = [...($this->perfiles[$id] ?? ['id' => $id]), ...$fields];

        return $this->perfiles[$id];
    }

    public function delete(string $id): void
    {
        unset($this->perfiles[$id]);
    }
}
