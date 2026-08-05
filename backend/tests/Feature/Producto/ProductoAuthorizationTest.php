<?php

namespace Tests\Feature\Producto;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\ProductoRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\InMemoryBitacoraRepository;
use Tests\Support\Fakes\InMemoryPerfilRepository;
use Tests\Support\Fakes\InMemoryProductoRepository;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class ProductoAuthorizationTest extends TestCase
{
    private InMemoryUserRepository $usuarios;

    private InMemoryPerfilRepository $perfiles;

    private InMemoryProductoRepository $productos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuarios = new InMemoryUserRepository();
        $this->perfiles = new InMemoryPerfilRepository();
        $this->productos = new InMemoryProductoRepository();

        $this->app->instance(UserRepositoryInterface::class, $this->usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $this->usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $this->perfiles);
        $this->app->instance(ProductoRepositoryInterface::class, $this->productos);
        $this->app->instance(BitacoraRepositoryInterface::class, new InMemoryBitacoraRepository());
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
    }

    private function loginAs(string $email): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'Secret123',
        ])->json('data.token');
    }

    public function test_authenticated_user_without_perfiles_can_consult_but_not_write(): void
    {
        $this->usuarios->seed('user-1', 'Sin Permisos', 'sinpermisos@example.com', Hash::make('Secret123'));

        $token = $this->loginAs('sinpermisos@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/productos')
            ->assertStatus(200);

        $producto = $this->productos->create(['name' => 'Silla', 'precio' => 100, 'created_at' => now()]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/productos/{$producto['id']}")
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/productos', ['name' => 'Mesa', 'precio' => 200])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/productos/{$producto['id']}", ['name' => 'Silla 2', 'precio' => 150])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/productos/{$producto['id']}")
            ->assertStatus(403);
    }

    public function test_user_with_partial_permissions_is_restricted_accordingly(): void
    {
        $this->perfiles->create([
            'name' => 'Solo Crear',
            'secciones' => [
                ['seccion' => 'productos', 'crear' => true, 'consultar' => true, 'editar' => false, 'eliminar' => false],
            ],
            'created_at' => now(),
        ], 'perfil-crear');

        $this->usuarios->seed('user-2', 'Solo Crear', 'solocrear@example.com', Hash::make('Secret123'));
        $this->usuarios->update('user-2', ['perfiles' => ['perfil-crear']]);

        $token = $this->loginAs('solocrear@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/productos', ['name' => 'Lámpara', 'precio' => 120])
            ->assertStatus(201);

        $producto = $this->productos->create(['name' => 'Silla', 'precio' => 100, 'created_at' => now()]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/productos/{$producto['id']}", ['name' => 'Silla 2', 'precio' => 150])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/productos/{$producto['id']}")
            ->assertStatus(403);
    }

    public function test_login_and_me_expose_permisos_matrix(): void
    {
        $this->perfiles->create([
            'name' => 'Administrador',
            'secciones' => [
                ['seccion' => 'productos', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-admin');

        $this->usuarios->seed('admin-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));
        $this->usuarios->update('admin-1', ['perfiles' => ['perfil-admin']]);

        $login = $this->postJson('/api/v1/auth/login', ['email' => 'admin@example.com', 'password' => 'Secret123']);

        $login->assertStatus(200)->assertJsonPath('data.permisos', function (array $permisos) {
            foreach ($permisos as $entry) {
                if ($entry['seccion'] === 'productos') {
                    return $entry['crear'] === true && $entry['eliminar'] === true;
                }
            }

            return false;
        });

        $token = $login->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'permisos']]);
    }
}
