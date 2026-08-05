<?php

namespace App\DTOs;

readonly class AuthenticatedUserDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public array $perfiles,
    ) {
    }

    public static function fromArray(array $user): self
    {
        return new self(
            id: $user['id'],
            name: $user['name'],
            email: $user['email'],
            perfiles: $user['perfiles'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'perfiles' => $this->perfiles,
        ];
    }
}
