<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Requests\Auth\LoginRequest;
use App\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use App\Services\Permission\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PermissionService $permissions,
    ) {
    }

    #[OA\Post(
        path: '/auth/login',
        operationId: 'authLogin',
        summary: 'Inicia sesión con correo electrónico y contraseña',
        description: 'Valida que el usuario exista en Firebase Firestore y que la contraseña sea correcta. Si las credenciales son válidas, genera un token de acceso temporal (Bearer) que debe enviarse en el header Authorization de las siguientes peticiones protegidas.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Inicio de sesión exitoso',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales incorrectas',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación de los datos enviados',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: 'Demasiados intentos de inicio de sesión',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->string('email')->toString(), $request->string('password')->toString());

        return ApiResponse::success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'expires_at' => $result['expires_at']->toIso8601String(),
            'permisos' => $this->permissions->aggregate($result['user']->perfiles),
        ], 'Inicio de sesión exitoso.');
    }

    #[OA\Post(
        path: '/auth/logout',
        operationId: 'authLogout',
        summary: 'Cierra la sesión del usuario autenticado',
        description: 'Invalida el token de acceso actual, eliminándolo del almacenamiento en Firebase Firestore. El cliente debe descartar el token localmente tras la respuesta.',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada correctamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado o sesión expirada',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout((string) $request->attributes->get('auth_token'));

        return ApiResponse::success(null, 'Sesión cerrada correctamente.');
    }

    #[OA\Get(
        path: '/auth/me',
        operationId: 'authMe',
        summary: 'Obtiene el usuario autenticado a partir del token actual',
        description: 'Permite al frontend validar y mantener la sesión activa tras recargar la aplicación, comprobando que el token Bearer siga siendo válido.',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión activa',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthMeResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado o sesión expirada',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
            ),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        $actor = $request->attributes->get('auth_user');

        return ApiResponse::success([
            'user' => new UserResource($actor),
            'permisos' => $this->permissions->aggregate($actor->perfiles),
        ], 'Sesión activa.');
    }
}
