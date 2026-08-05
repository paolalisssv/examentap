<?php

namespace App\Services\Auth;

use App\DTOs\AuthenticatedUserDTO;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\NotFoundException;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\PasswordRecoveryMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TokenRepositoryInterface $tokens,
    ) {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || ! Hash::check($password, $user['password'])) {
            throw new InvalidCredentialsException();
        }

        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes((int) config('firebase.auth_token_ttl_minutes'));

        $this->tokens->create($user['id'], $this->hashToken($token), $expiresAt);

        return [
            'user' => AuthenticatedUserDTO::fromArray($user),
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function logout(string $token): void
    {
        $this->tokens->delete($this->hashToken($token));
    }

    public function resolveUserFromToken(string $token): ?AuthenticatedUserDTO
    {
        $tokenRecord = $this->tokens->findValidByHash($this->hashToken($token));

        if ($tokenRecord === null) {
            return null;
        }

        $user = $this->users->findById($tokenRecord['user_id']);

        if ($user === null) {
            return null;
        }

        return AuthenticatedUserDTO::fromArray($user);
    }

    public function requestPasswordReset(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            throw new NotFoundException('No existe una cuenta asociada a este correo electrónico.');
        }

        $temporaryPassword = Str::password(14);

        $this->users->updatePassword($user['id'], Hash::make($temporaryPassword));

        Mail::to($user['email'])->send(new PasswordRecoveryMail($user['name'], $temporaryPassword));
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
