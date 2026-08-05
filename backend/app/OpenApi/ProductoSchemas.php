<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductoListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Productos obtenidos correctamente.'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/Producto')),
                new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination'),
            ]
        ),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'ProductoResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Producto creado correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Producto'),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
class ProductoSchemas
{
}
