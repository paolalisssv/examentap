<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Perfil\PerfilController;
use App\Http\Controllers\Producto\ProductoController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Usuario\UsuarioController;
use App\Http\Middleware\AuthorizeUsuarioCreation;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index']);
    Route::get('/system/status', [SystemController::class, 'status']);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:6,1');

        Route::middleware('auth.token')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::prefix('usuarios')->group(function () {
        Route::post('/', [UsuarioController::class, 'store'])->middleware(AuthorizeUsuarioCreation::class);

        Route::middleware('auth.token')->group(function () {
            Route::middleware('permission:usuarios,consultar')->group(function () {
                Route::get('/', [UsuarioController::class, 'index']);
                Route::get('/export/pdf', [UsuarioController::class, 'exportPdf']);
                Route::get('/export/excel', [UsuarioController::class, 'exportExcel']);
                Route::get('/{usuario}', [UsuarioController::class, 'show']);
            });
            Route::put('/{usuario}', [UsuarioController::class, 'update'])->middleware('permission:usuarios,editar');
            Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permission:usuarios,eliminar');
        });
    });

    Route::middleware('auth.token')->group(function () {
        Route::prefix('perfiles')->group(function () {
            Route::middleware('permission:perfiles,consultar')->group(function () {
                Route::get('/', [PerfilController::class, 'index']);
                Route::get('/export/pdf', [PerfilController::class, 'exportPdf']);
                Route::get('/export/excel', [PerfilController::class, 'exportExcel']);
                Route::get('/{perfil}', [PerfilController::class, 'show']);
            });
            Route::post('/', [PerfilController::class, 'store'])->middleware('permission:perfiles,crear');
            Route::put('/{perfil}', [PerfilController::class, 'update'])->middleware('permission:perfiles,editar');
            Route::delete('/{perfil}', [PerfilController::class, 'destroy'])->middleware('permission:perfiles,eliminar');
        });

        Route::prefix('productos')->group(function () {
            Route::get('/', [ProductoController::class, 'index']);
            Route::get('/export/pdf', [ProductoController::class, 'exportPdf']);
            Route::get('/export/excel', [ProductoController::class, 'exportExcel']);
            Route::get('/{producto}', [ProductoController::class, 'show']);
            Route::post('/', [ProductoController::class, 'store'])->middleware('permission:productos,crear');
            Route::put('/{producto}', [ProductoController::class, 'update'])->middleware('permission:productos,editar');
            Route::delete('/{producto}', [ProductoController::class, 'destroy'])->middleware('permission:productos,eliminar');
        });
    });
});
