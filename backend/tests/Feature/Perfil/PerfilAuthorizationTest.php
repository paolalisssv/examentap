<?php

namespace Tests\Feature\Perfil;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\FileStorageInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\ProductoRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\FakeFileStorage;
use Tests\Support\Fakes\InMemoryBitacoraRepository;
use Tests\Support\Fakes\InMemoryPerfilRepository;
use Tests\Support\Fakes\InMemoryProductoRepository;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class PerfilAuthorizationTest extends TestCase
{
    private InMemoryUserRepository $usuarios;

    private InMemoryPerfilRepository $perfiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuarios = new InMemoryUserRepository();
        $this->perfiles = new InMemoryPerfilRepository();

        $this->app->instance(UserRepositoryInterface::class, $this->usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $this->usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $this->perfiles);
        $this->app->instance(ProductoRepositoryInterface::class, new InMemoryProductoRepository());
        $this->app->instance(BitacoraRepositoryInterface::class, new InMemoryBitacoraRepository());
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
        $this->app->instance(FileStorageInterface::class, new FakeFileStorage());
    }

    private function loginAs(string $email): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'Secret123',
        ])->json('data.token');
    }

    public function test_user_with_only_productos_permissions_cannot_access_usuarios_or_perfiles(): void
    {
        $this->perfiles->create([
            'name' => 'Solo Productos',
            'secciones' => [
                ['seccion' => 'productos', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-productos');

        $this->usuarios->seed('user-1', 'Solo Productos', 'soloproductos@example.com', Hash::make('Secret123'));
        $this->usuarios->update('user-1', ['perfiles' => ['perfil-productos']]);

        $token = $this->loginAs('soloproductos@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/usuarios')
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/perfiles')
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/perfiles', ['name' => 'Nuevo'])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/productos')
            ->assertStatus(200);
    }

    public function test_user_without_perfiles_editar_cannot_update_perfil(): void
    {
        $this->perfiles->create([
            'name' => 'Solo Consulta',
            'secciones' => [
                ['seccion' => 'perfiles', 'crear' => false, 'consultar' => true, 'editar' => false, 'eliminar' => false],
            ],
            'created_at' => now(),
        ], 'perfil-consulta');

        $this->usuarios->seed('user-2', 'Solo Consulta', 'soloconsulta@example.com', Hash::make('Secret123'));
        $this->usuarios->update('user-2', ['perfiles' => ['perfil-consulta']]);

        $token = $this->loginAs('soloconsulta@example.com');

        $objetivo = $this->perfiles->create(['name' => 'Objetivo', 'secciones' => [], 'created_at' => now()]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/perfiles')
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/perfiles/{$objetivo['id']}", ['name' => 'Cambiado'])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/perfiles/{$objetivo['id']}")
            ->assertStatus(403);
    }
}
