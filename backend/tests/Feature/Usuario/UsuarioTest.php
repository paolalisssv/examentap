<?php

namespace Tests\Feature\Usuario;

use App\Interfaces\BitacoraRepositoryInterface;
use App\Interfaces\FileStorageInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Fakes\FakeFileStorage;
use Tests\Support\Fakes\InMemoryBitacoraRepository;
use Tests\Support\Fakes\InMemoryPerfilRepository;
use Tests\Support\Fakes\InMemoryTokenRepository;
use Tests\Support\Fakes\InMemoryUserRepository;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    private InMemoryUserRepository $usuarios;

    private InMemoryPerfilRepository $perfiles;

    private InMemoryBitacoraRepository $bitacora;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuarios = new InMemoryUserRepository();
        $this->perfiles = new InMemoryPerfilRepository();
        $this->bitacora = new InMemoryBitacoraRepository();

        $this->perfiles->create([
            'name' => 'Administrador',
            'secciones' => [
                ['seccion' => 'usuarios', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-admin');

        $this->usuarios->seed('admin-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));
        $this->usuarios->update('admin-1', ['perfiles' => ['perfil-admin']]);

        $this->app->instance(UserRepositoryInterface::class, $this->usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $this->usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $this->perfiles);
        $this->app->instance(BitacoraRepositoryInterface::class, $this->bitacora);
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

    public function test_index_lists_search_sorts_and_paginates(): void
    {
        $this->usuarios->seed('user-a', 'Ana López', 'ana@example.com', Hash::make('Secret123'));
        $this->usuarios->seed('user-b', 'Beto Cruz', 'beto@example.com', Hash::make('Secret123'));

        $response = $this->authenticated()->getJson('/api/v1/usuarios?search=ana');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.email', 'ana@example.com');

        $paginated = $this->authenticated()->getJson('/api/v1/usuarios?per_page=1&page=1&sort_field=email&sort_direction=asc');

        $paginated->assertStatus(200)
            ->assertJsonPath('data.pagination.per_page', 1)
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_store_creates_usuario_uploads_photo_and_records_bitacora(): void
    {
        $response = $this->authenticated()->post('/api/v1/usuarios', [
            'name' => 'Carla Ruiz',
            'email' => 'carla@example.com',
            'password' => 'Secret123!',
            'telefono' => '+521234567890',
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'carla@example.com')
            ->assertJsonPath('data.foto_url', fn (string $url) => str_contains($url, 'fake-storage.test'));

        $this->assertCount(1, $this->bitacora->entries);
        $this->assertSame('alta', $this->bitacora->entries[0]['tipo']);
        $this->assertNull($this->bitacora->entries[0]['datos_anteriores']);
    }

    public function test_store_fails_with_duplicate_email(): void
    {
        $response = $this->authenticated()->post('/api/v1/usuarios', [
            'name' => 'Duplicado',
            'email' => 'admin@example.com',
            'password' => 'Secret123!',
            'foto' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_store_requires_mandatory_fields(): void
    {
        $response = $this->authenticated()->postJson('/api/v1/usuarios', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'password', 'foto']);
    }

    public function test_update_captures_before_and_after_in_bitacora(): void
    {
        $this->usuarios->seed('user-c', 'Carlos Vega', 'carlos@example.com', Hash::make('Secret123'));

        $response = $this->authenticated()->put('/api/v1/usuarios/user-c', [
            'name' => 'Carlos Vega Actualizado',
            'email' => 'carlos@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.name', 'Carlos Vega Actualizado');

        $entry = $this->bitacora->entries[0];
        $this->assertSame('edicion', $entry['tipo']);
        $this->assertSame('Carlos Vega', $entry['datos_anteriores']['name']);
        $this->assertSame('Carlos Vega Actualizado', $entry['datos_nuevos']['name']);
    }

    public function test_show_returns_detail_with_perfiles_and_secciones(): void
    {
        $this->perfiles->create([
            'name' => 'Supervisor',
            'secciones' => [
                ['seccion' => 'usuarios', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => false],
            ],
            'created_at' => now(),
        ], 'perfil-supervisor');

        $this->usuarios->seed('user-d', 'Dana Ríos', 'dana@example.com', Hash::make('Secret123'));
        $this->usuarios->update('user-d', ['perfiles' => ['perfil-supervisor']]);

        $response = $this->authenticated()->getJson('/api/v1/usuarios/user-d');

        $response->assertStatus(200)
            ->assertJsonPath('data.perfiles.0.name', 'Supervisor')
            ->assertJsonPath('data.secciones.0.crear', true);
    }

    public function test_destroy_removes_usuario(): void
    {
        $this->usuarios->seed('user-e', 'Eva Soto', 'eva@example.com', Hash::make('Secret123'));

        $this->authenticated()->deleteJson('/api/v1/usuarios/user-e')->assertStatus(200);

        $this->assertNull($this->usuarios->find('user-e'));
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/usuarios')->assertStatus(401);
    }
}
