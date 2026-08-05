<?php

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFirestoreToken
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            throw new AuthenticationException('No autenticado.');
        }

        $user = $this->authService->resolveUserFromToken($token);

        if ($user === null) {
            throw new AuthenticationException('Sesión expirada o inválida. Inicia sesión nuevamente.');
        }

        $request->attributes->set('auth_user', $user);
        $request->attributes->set('auth_token', $token);

        return $next($request);
    }
}
