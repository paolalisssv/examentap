<?php

namespace Tests\Feature\Auth;

use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\PasswordRecoveryMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
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

    public function test_forgot_password_sends_email_when_user_exists(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'juan@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => 'success']);

        Mail::assertSent(PasswordRecoveryMail::class, fn ($mail) => $mail->hasTo('juan@example.com'));

        $this->assertNotSame(
            Hash::make('Secret123'),
            $this->users->findByEmail('juan@example.com')['password']
        );
    }

    public function test_forgot_password_returns_not_found_when_user_does_not_exist(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'no-existe@example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson(['success' => false, 'status' => 'error']);

        Mail::assertNothingSent();
    }

    public function test_forgot_password_requires_a_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'not-an-email']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
