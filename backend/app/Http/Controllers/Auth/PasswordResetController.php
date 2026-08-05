<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PasswordResetController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    #[OA\Post(
        path: '/auth/forgot-password',
        operationId: 'authForgotPassword',
        summary: 'Solicita la recuperación de contraseña',
        description: 'Valida que el correo electrónico exista en Firebase Firestore. Si existe, genera una nueva contraseña temporal, la almacena hasheada y la envía al correo electrónico registrado mediante una plantilla HTML. Si el correo no existe, devuelve un mensaje indicándolo sin revelar más información sensible.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Credenciales enviadas al correo electrónico registrado',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'No existe una cuenta asociada al correo electrónico',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación de los datos enviados',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: 'Demasiadas solicitudes de recuperación de contraseña',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->requestPasswordReset($request->string('email')->toString());

        return ApiResponse::success(null, 'Se han enviado las credenciales al correo electrónico registrado.');
    }
}
