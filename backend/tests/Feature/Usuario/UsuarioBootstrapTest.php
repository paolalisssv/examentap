<?php

namespace Tests\Feature\Usuario;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\FileStorageInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\ProductoRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\FakeFileStorage;
use Tests\Support\Fakes\InMemoryBitacoraRepository;
use Tests\Support\Fakes\InMemoryPerfilRepository;
use Tests\Support\Fakes\InMemoryProductoRepository;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class UsuarioBootstrapTest extends TestCase
{
    private InMemoryUserRepository $usuarios;

    private InMemoryPerfilRepository $perfiles;

    private InMemoryBitacoraRepository $bitacora;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuarios = new InMemoryUserRepository();
        $this->perfiles = new InMemoryPerfilRepository();
        $this->bitacora = new InMemoryBitacoraRepository();

        $this->app->instance(UserRepositoryInterface::class, $this->usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $this->usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $this->perfiles);
        $this->app->instance(ProductoRepositoryInterface::class, new InMemoryProductoRepository());
        $this->app->instance(BitacoraRepositoryInterface::class, $this->bitacora);
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
        $this->app->instance(FileStorageInterface::class, new FakeFileStorage());
    }

    public function test_first_user_can_be_created_without_authentication_and_becomes_administrador(): void
    {
        $response = $this->post('/api/v1/usuarios', [
            'name' => 'Primer Admin',
            'email' => 'primeradmin@example.com',
            'password' => 'Secret123!',
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $response->assertStatus(201)->assertJsonPath('data.email', 'primeradmin@example.com');

        $created = $this->usuarios->findByEmail('primeradmin@example.com');
        $this->assertNotNull($created);
        $this->assertCount(1, $created['perfiles']);

        $perfil = $this->perfiles->find($created['perfiles'][0]);
        $this->assertSame('Administrador', $perfil['name']);

        foreach ($perfil['secciones'] as $seccion) {
            $this->assertTrue($seccion['crear']);
            $this->assertTrue($seccion['consultar']);
            $this->assertTrue($seccion['editar']);
            $this->assertTrue($seccion['eliminar']);
        }

        $this->assertCount(1, $this->bitacora->entries);
        $this->assertSame($created['id'], $this->bitacora->entries[0]['realizado_por']['id']);
    }

    public function test_creation_requires_authentication_once_a_user_exists(): void
    {
        $this->usuarios->seed('user-1', 'Existente', 'existente@example.com', Hash::make('Secret123'));

        $this->postJson('/api/v1/usuarios', [
            'name' => 'Otro',
            'email' => 'otro@example.com',
            'password' => 'Secret123!',
        ])->assertStatus(401);
    }

    public function test_creation_requires_usuarios_crear_permission_once_a_user_exists(): void
    {
        $this->usuarios->seed('user-1', 'Sin Permisos', 'sinpermisos@example.com', Hash::make('Secret123'));

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'sinpermisos@example.com',
            'password' => 'Secret123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/usuarios', [
                'name' => 'Otro',
                'email' => 'otro@example.com',
                'password' => 'Secret123!',
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertStatus(403);
    }

    public function test_creation_succeeds_with_usuarios_crear_permission(): void
    {
        $this->perfiles->create([
            'name' => 'Administrador',
            'secciones' => [
                ['seccion' => 'usuarios', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-admin');

        $this->usuarios->seed('user-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));
        $this->usuarios->update('user-1', ['perfiles' => ['perfil-admin']]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Secret123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/usuarios', [
                'name' => 'Otro',
                'email' => 'otro@example.com',
                'password' => 'Secret123!',
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertStatus(201);
    }
}
