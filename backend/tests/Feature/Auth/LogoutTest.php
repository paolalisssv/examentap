<?php

namespace Tests\Feature\Auth;

use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $users = new InMemoryUserRepository();
        $users->seed('user-1', 'Juan Pérez', 'juan@example.com', Hash::make('Secret123'));

        $this->app->instance(UserRepositoryInterface::class, $users);
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'status' => 'error']);
    }

    public function test_logout_invalidates_the_token(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'juan@example.com',
            'password' => 'Secret123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'success']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function test_me_returns_current_user_with_valid_token(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'juan@example.com',
            'password' => 'Secret123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.user.email', 'juan@example.com');
    }
}
