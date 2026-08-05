<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

readonly class UsuarioDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $telefono,
        public string $fotoUrl,
        public array $perfiles,
        public Carbon $createdAt,
    ) {
    }

    public static function fromArray(array $usuario): self
    {
        return new self(
            id: $usuario['id'],
            name: $usuario['name'] ?? '',
            email: $usuario['email'],
            telefono: $usuario['telefono'] ?? null,
            fotoUrl: $usuario['foto_url'] ?? '',
            perfiles: $usuario['perfiles'] ?? [],
            createdAt: $usuario['created_at'] instanceof Carbon ? $usuario['created_at'] : Carbon::parse($usuario['created_at']),
        );
    }
}
