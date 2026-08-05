<?php

namespace App\Resources\Producto;

use App\Resources\BaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Producto',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'a1b2c3d4e5f6'),
        new OA\Property(property: 'name', type: 'string', example: 'Teclado mecánico'),
        new OA\Property(property: 'precio', type: 'number', format: 'float', example: 599.99),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-08-05T13:00:00+00:00'),
    ]
)]
class ProductoResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'precio' => $this->precio,
            'created_at' => $this->createdAt->toIso8601String(),
        ];
    }
}
