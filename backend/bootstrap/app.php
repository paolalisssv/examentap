<?php

use App\Helpers\ApiResponse;
use App\Http\Middleware\AuthenticateFirestoreToken;
use App\Http\Middleware\AuthorizePermission;
use App\Http\Middleware\ForceJsonResponseMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->api(prepend: [
            ForceJsonResponseMiddleware::class,
        ]);

        $middleware->alias([
            'auth.token' => AuthenticateFirestoreToken::class,
            'permission' => AuthorizePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (ValidationException $exception) {
            return ApiResponse::error('Validation error', $exception->errors(), 422);
        });

        $exceptions->render(function (AuthenticationException $exception) {
            return ApiResponse::error($exception->getMessage(), null, 401);
        });

        $exceptions->render(function (ModelNotFoundException $exception) {
            return ApiResponse::error('Resource not found', null, 404);
        });

        $exceptions->render(function (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage() ?: 'HTTP error', null, $exception->getStatusCode());
        });

        $exceptions->render(function (\Throwable $exception) {
            return ApiResponse::error(
                config('app.debug') ? $exception->getMessage() : 'Server error',
                null,
                500
            );
        });
    })->create();
