<?php

namespace Tests\Support\Fakes;

use App\Interfaces\BitacoraRepositoryInterface;

class InMemoryBitacoraRepository implements BitacoraRepositoryInterface
{
    public array $entries = [];

    public function create(array $fields): array
    {
        $entry = ['id' => 'bitacora-'.count($this->entries), ...$fields];
        $this->entries[] = $entry;

        return $entry;
    }
}
