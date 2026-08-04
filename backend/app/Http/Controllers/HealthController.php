<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/health',
        summary: 'Verifica el estado de la API',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'La API está operativa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'OK'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'app', type: 'string', example: 'ExamenTAP'),
                            new OA\Property(property: 'timestamp', type: 'string', example: '2026-01-01T00:00:00+00:00'),
                        ]),
                        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'app' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ], 'OK');
    }
}
