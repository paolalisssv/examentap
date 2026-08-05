<?php

namespace App\Providers;

use App\Interfaces\FileStorageInterface;
use App\Services\Firebase\FirebaseService;
use App\Services\Storage\LocalFileStorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseService::class);
        $this->app->bind(FileStorageInterface::class, LocalFileStorageService::class);
    }

    public function boot(): void
    {
    }
}
