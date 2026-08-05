<?php

namespace App\Resources\Perfil;

use App\Resources\BaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Perfil',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'a1b2c3d4e5f6'),
        new OA\Property(property: 'name', type: 'string', example: 'Administrador'),
        new OA\Property(
            property: 'secciones',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'seccion', type: 'string', example: 'usuarios'),
                    new OA\Property(property: 'crear', type: 'boolean', example: true),
                    new OA\Property(property: 'consultar', type: 'boolean', example: true),
                    new OA\Property(property: 'editar', type: 'boolean', example: true),
                    new OA\Property(property: 'eliminar', type: 'boolean', example: false),
                ]
            )
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-08-05T13:00:00+00:00'),
    ]
)]
class PerfilResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'secciones' => $this->secciones,
            'created_at' => $this->createdAt->toIso8601String(),
        ];
    }
}
