<?php

namespace App\Resources\Usuario;

use App\Resources\BaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Usuario',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'f3b1c2d4e5f6'),
        new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.perez@example.com'),
        new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+521234567890'),
        new OA\Property(property: 'foto_url', type: 'string', example: 'https://storage.googleapis.com/bucket/usuarios/abc.jpg'),
        new OA\Property(property: 'perfiles', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-08-05T13:00:00+00:00'),
    ]
)]
class UsuarioResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'foto_url' => $this->fotoUrl,
            'perfiles' => $this->perfiles,
            'created_at' => $this->createdAt->toIso8601String(),
        ];
    }
}
