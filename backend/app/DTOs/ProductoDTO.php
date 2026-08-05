<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

readonly class ProductoDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public float $precio,
        public Carbon $createdAt,
    ) {
    }

    public static function fromArray(array $producto): self
    {
        return new self(
            id: $producto['id'],
            name: $producto['name'] ?? '',
            precio: (float) ($producto['precio'] ?? 0),
            createdAt: $producto['created_at'] instanceof Carbon ? $producto['created_at'] : Carbon::parse($producto['created_at']),
        );
    }
}
