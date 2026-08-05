<?php

namespace Tests\Feature\Perfil;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\FileStorageInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\FakeFileStorage;
use Tests\Support\Fakes\InMemoryBitacoraRepository;
use Tests\Support\Fakes\InMemoryPerfilRepository;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    private InMemoryPerfilRepository $perfiles;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $usuarios = new InMemoryUserRepository();
        $this->perfiles = new InMemoryPerfilRepository();

        $this->perfiles->create([
            'name' => 'Acceso Total',
            'secciones' => [
                ['seccion' => 'perfiles', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-actor');

        $usuarios->seed('admin-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));
        $usuarios->update('admin-1', ['perfiles' => ['perfil-actor']]);

        $this->app->instance(UserRepositoryInterface::class, $usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $this->perfiles);
        $this->app->instance(BitacoraRepositoryInterface::class, new InMemoryBitacoraRepository());
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
        $this->app->instance(FileStorageInterface::class, new FakeFileStorage());

        $this->token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Secret123',
        ])->json('data.token');
    }

    private function authenticated(): self
    {
        $this->withHeader('Authorization', "Bearer {$this->token}");

        return $this;
    }

    public function test_index_lists_and_searches_perfiles(): void
    {
        $this->perfiles->create(['name' => 'Administrador', 'secciones' => [], 'created_at' => now()]);
        $this->perfiles->create(['name' => 'Operador', 'secciones' => [], 'created_at' => now()]);

        $response = $this->authenticated()->getJson('/api/v1/perfiles?search=admin');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Administrador');
    }

    public function test_store_creates_perfil_with_secciones(): void
    {
        $response = $this->authenticated()->postJson('/api/v1/perfiles', [
            'name' => 'Administrador',
            'secciones' => [
                ['seccion' => 'usuarios', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => false],
                ['seccion' => 'productos', 'crear' => false, 'consultar' => true, 'editar' => false, 'eliminar' => false],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Administrador')
            ->assertJsonCount(2, 'data.secciones');
    }

    public function test_store_fails_with_duplicate_name(): void
    {
        $this->perfiles->create(['name' => 'Administrador', 'secciones' => [], 'created_at' => now()]);

        $response = $this->authenticated()->postJson('/api/v1/perfiles', ['name' => 'Administrador']);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_store_rejects_invalid_seccion(): void
    {
        $response = $this->authenticated()->postJson('/api/v1/perfiles', [
            'name' => 'Nuevo',
            'secciones' => [['seccion' => 'facturacion', 'crear' => true]],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['secciones.0.seccion']);
    }

    public function test_update_modifies_perfil(): void
    {
        $perfil = $this->perfiles->create(['name' => 'Operador', 'secciones' => [], 'created_at' => now()]);

        $response = $this->authenticated()->putJson("/api/v1/perfiles/{$perfil['id']}", [
            'name' => 'Operador Senior',
            'secciones' => [],
        ]);

        $response->assertStatus(200)->assertJsonPath('data.name', 'Operador Senior');
    }

    public function test_destroy_removes_perfil(): void
    {
        $perfil = $this->perfiles->create(['name' => 'Temporal', 'secciones' => [], 'created_at' => now()]);

        $this->authenticated()->deleteJson("/api/v1/perfiles/{$perfil['id']}")->assertStatus(200);

        $this->assertNull($this->perfiles->find($perfil['id']));
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $this->authenticated()->getJson('/api/v1/perfiles/no-existe')->assertStatus(404);
    }
}
