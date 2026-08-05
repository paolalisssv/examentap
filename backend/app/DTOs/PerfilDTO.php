<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

readonly class PerfilDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public array $secciones,
        public Carbon $createdAt,
    ) {
    }

    public static function fromArray(array $perfil): self
    {
        return new self(
            id: $perfil['id'],
            name: $perfil['name'] ?? '',
            secciones: $perfil['secciones'] ?? [],
            createdAt: $perfil['created_at'] instanceof Carbon ? $perfil['created_at'] : Carbon::parse($perfil['created_at']),
        );
    }
}
