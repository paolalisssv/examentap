<?php

namespace App\Providers;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\ProductoRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use App\Repositories\Firestore\FirestoreBitacoraRepository;
use App\Repositories\Firestore\FirestorePerfilRepository;
use App\Repositories\Firestore\FirestoreProductoRepository;
use App\Repositories\Firestore\FirestoreTokenRepository;
use App\Repositories\Firestore\FirestoreUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, FirestoreUserRepository::class);
        $this->app->bind(UsuarioRepositoryInterface::class, FirestoreUserRepository::class);
        $this->app->bind(TokenRepositoryInterface::class, FirestoreTokenRepository::class);
        $this->app->bind(PerfilRepositoryInterface::class, FirestorePerfilRepository::class);
        $this->app->bind(BitacoraRepositoryInterface::class, FirestoreBitacoraRepository::class);
        $this->app->bind(ProductoRepositoryInterface::class, FirestoreProductoRepository::class);
    }

    public function boot(): void
    {
    }
}
