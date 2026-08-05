<?php

namespace Tests\Feature\Auth;

use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new InMemoryUserRepository();
        $this->users->seed('user-1', 'Juan Pérez', 'juan@example.com', Hash::make('Secret123'));

        $this->app->instance(UserRepositoryInterface::class, $this->users);
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'juan@example.com',
            'password' => 'Secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'success'])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_at',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'juan@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'status' => 'error']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'no-existe@example.com',
            'password' => 'Secret123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'status' => 'error']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
