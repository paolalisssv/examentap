<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Pagination',
    type: 'object',
    properties: [
        new OA\Property(property: 'page', type: 'integer', example: 1),
        new OA\Property(property: 'per_page', type: 'integer', example: 10),
        new OA\Property(property: 'total', type: 'integer', example: 42),
        new OA\Property(property: 'total_pages', type: 'integer', example: 5),
    ]
)]
#[OA\Schema(
    schema: 'UsuarioListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Usuarios obtenidos correctamente.'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/Usuario')),
                new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination'),
            ]
        ),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'UsuarioResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Usuario creado correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Usuario'),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'UsuarioDetailResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Detalle de usuario obtenido correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/UsuarioDetail'),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
class UsuarioSchemas
{
}
