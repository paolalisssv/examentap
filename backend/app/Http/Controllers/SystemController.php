<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\System\SystemService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SystemController extends Controller
{
    public function __construct(private readonly SystemService $systemService)
    {
    }

    #[OA\Get(
        path: '/system/status',
        operationId: 'systemStatus',
        summary: 'Consulta si el sistema ya cuenta con usuarios registrados',
        description: 'Endpoint público (no requiere autenticación) utilizado por el frontend para decidir si debe mostrarse el formulario público de Alta de Usuario inicial (sistema sin usuarios) o la pantalla de Login (sistema ya inicializado).',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado del sistema obtenido correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Estado del sistema obtenido correctamente.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'initialized', type: 'boolean', example: false),
                            ]
                        ),
                        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
                    ],
                    examples: [
                        new OA\Examples(
                            example: 'sin_usuarios',
                            summary: 'El sistema no tiene ningún usuario registrado',
                            value: [
                                'success' => true,
                                'status' => 'success',
                                'message' => 'Estado del sistema obtenido correctamente.',
                                'data' => ['initialized' => false],
                                'errors' => null,
                            ]
                        ),
                        new OA\Examples(
                            example: 'con_usuarios',
                            summary: 'El sistema ya tiene al menos un usuario registrado',
                            value: [
                                'success' => true,
                                'status' => 'success',
                                'message' => 'Estado del sistema obtenido correctamente.',
                                'data' => ['initialized' => true],
                                'errors' => null,
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function status(): JsonResponse
    {
        return ApiResponse::success([
            'initialized' => $this->systemService->isInitialized(),
        ], 'Estado del sistema obtenido correctamente.');
    }
}
