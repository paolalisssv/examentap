<?php

namespace App\Providers;

use App\Services\Firebase\FirebaseService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseService::class);
    }

    public function boot(): void
    {
    }
}
