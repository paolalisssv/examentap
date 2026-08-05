<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthLoginResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Inicio de sesión exitoso.'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                new OA\Property(property: 'token', type: 'string', example: '5f3c9b1e8a7d6c2f4b0a9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b3a2f1e0d9c8b'),
                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2026-08-05T13:00:00+00:00'),
                new OA\Property(property: 'permisos', type: 'array', items: new OA\Items(ref: '#/components/schemas/SeccionPermiso')),
            ]
        ),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'AuthMeResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Sesión activa.'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                new OA\Property(property: 'permisos', type: 'array', items: new OA\Items(ref: '#/components/schemas/SeccionPermiso')),
            ]
        ),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'SeccionPermiso',
    type: 'object',
    properties: [
        new OA\Property(property: 'seccion', type: 'string', example: 'productos'),
        new OA\Property(property: 'crear', type: 'boolean', example: true),
        new OA\Property(property: 'consultar', type: 'boolean', example: true),
        new OA\Property(property: 'editar', type: 'boolean', example: true),
        new OA\Property(property: 'eliminar', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'ApiSuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Operación exitosa.'),
        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'ApiErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Las credenciales proporcionadas son incorrectas.'),
        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'ApiValidationErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Validation error'),
        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['email' => ['El correo electrónico es obligatorio.']]
        ),
    ]
)]
class AuthSchemas
{
}
