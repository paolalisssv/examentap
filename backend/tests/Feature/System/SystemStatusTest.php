<?php

namespace Tests\Feature\System;

use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    public function test_status_reports_not_initialized_when_no_users_exist(): void
    {
        $this->app->instance(UsuarioRepositoryInterface::class, new InMemoryUserRepository());

        $this->getJson('/api/v1/system/status')
            ->assertStatus(200)
            ->assertJsonPath('data.initialized', false);
    }

    public function test_status_reports_initialized_when_a_user_exists(): void
    {
        $usuarios = new InMemoryUserRepository();
        $usuarios->seed('user-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));

        $this->app->instance(UsuarioRepositoryInterface::class, $usuarios);

        $this->getJson('/api/v1/system/status')
            ->assertStatus(200)
            ->assertJsonPath('data.initialized', true);
    }
}
