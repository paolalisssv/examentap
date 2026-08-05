<?php

namespace App\Resources\Usuario;

use App\Resources\BaseResource;
use App\Resources\Perfil\PerfilResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UsuarioDetail',
    type: 'object',
    properties: [
        new OA\Property(property: 'usuario', ref: '#/components/schemas/Usuario'),
        new OA\Property(property: 'perfiles', type: 'array', items: new OA\Items(ref: '#/components/schemas/Perfil')),
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
    ]
)]
class UsuarioDetailResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'usuario' => new UsuarioResource($this->resource['usuario']),
            'perfiles' => PerfilResource::collection($this->resource['perfiles']),
            'secciones' => $this->resource['secciones'],
        ];
    }
}
