<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthorizationDeniedException;
use App\Interfaces\UsuarioRepositoryInterface;
use App\Services\Auth\AuthService;
use App\Services\Permission\PermissionService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeUsuarioCreation
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
        private readonly AuthService $authService,
        private readonly PermissionService $permissions,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->usuarios->any()) {
            $request->attributes->set('bootstrap', true);

            return $next($request);
        }

        $token = $request->bearerToken();

        if ($token === null) {
            throw new AuthenticationException('No autenticado.');
        }

        $actor = $this->authService->resolveUserFromToken($token);

        if ($actor === null) {
            throw new AuthenticationException('Sesión expirada o inválida. Inicia sesión nuevamente.');
        }

        if (! $this->permissions->puede($actor->perfiles, 'usuarios', 'crear')) {
            throw new AuthorizationDeniedException('No tienes permisos para crear usuarios.');
        }

        $request->attributes->set('auth_user', $actor);
        $request->attributes->set('auth_token', $token);
        $request->attributes->set('bootstrap', false);

        return $next($request);
    }
}
