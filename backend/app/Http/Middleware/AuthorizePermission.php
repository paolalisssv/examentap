<?php

namespace App\Http\Middleware;

use App\DTOs\AuthenticatedUserDTO;
use App\Exceptions\AuthorizationDeniedException;
use App\Services\Permission\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizePermission
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next, string $seccion, string $permiso): Response
    {
        $actor = $request->attributes->get('auth_user');

        if (! $actor instanceof AuthenticatedUserDTO || ! $this->permissions->puede($actor->perfiles, $seccion, $permiso)) {
            throw new AuthorizationDeniedException();
        }

        return $next($request);
    }
}
