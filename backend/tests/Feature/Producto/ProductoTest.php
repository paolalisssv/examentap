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

class ProductoTest extends TestCase
{
    private InMemoryProductoRepository $productos;

    private InMemoryBitacoraRepository $bitacora;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $usuarios = new InMemoryUserRepository();
        $perfiles = new InMemoryPerfilRepository();
        $this->productos = new InMemoryProductoRepository();
        $this->bitacora = new InMemoryBitacoraRepository();

        $perfiles->create([
            'name' => 'Administrador',
            'secciones' => [
                ['seccion' => 'productos', 'crear' => true, 'consultar' => true, 'editar' => true, 'eliminar' => true],
            ],
            'created_at' => now(),
        ], 'perfil-admin');

        $usuarios->seed('admin-1', 'Admin', 'admin@example.com', Hash::make('Secret123'));
        $usuarios->update('admin-1', ['perfiles' => ['perfil-admin']]);

        $this->app->instance(UserRepositoryInterface::class, $usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, $perfiles);
        $this->app->instance(ProductoRepositoryInterface::class, $this->productos);
        $this->app->instance(BitacoraRepositoryInterface::class, $this->bitacora);
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());

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
        $this->productos->create(['name' => 'Teclado', 'precio' => 599.99, 'created_at' => now()]);
        $this->productos->create(['name' => 'Mouse', 'precio' => 199.5, 'created_at' => now()]);

        $response = $this->authenticated()->getJson('/api/v1/productos?search=teclado');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Teclado');

        $sorted = $this->authenticated()->getJson('/api/v1/productos?sort_field=precio&sort_direction=asc');

        $sorted->assertStatus(200)->assertJsonPath('data.items.0.name', 'Mouse');
    }

    public function test_store_creates_producto_and_records_bitacora(): void
    {
        $response = $this->authenticated()->postJson('/api/v1/productos', [
            'name' => 'Monitor',
            'precio' => 899.99,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Monitor')
            ->assertJsonPath('data.precio', 899.99);

        $this->assertCount(1, $this->bitacora->entries);
        $this->assertSame('producto', $this->bitacora->entries[0]['entidad']);
        $this->assertSame('alta', $this->bitacora->entries[0]['tipo']);
        $this->assertNull($this->bitacora->entries[0]['datos_anteriores']);
    }

    public function test_store_requires_name_and_precio(): void
    {
        $this->authenticated()->postJson('/api/v1/productos', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'precio']);
    }

    public function test_store_rejects_negative_precio(): void
    {
        $this->authenticated()->postJson('/api/v1/productos', ['name' => 'Producto', 'precio' => -5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['precio']);
    }

    public function test_store_rejects_precio_with_more_than_three_digits(): void
    {
        $this->authenticated()->postJson('/api/v1/productos', ['name' => 'Producto', 'precio' => 1000])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['precio']);
    }

    public function test_update_captures_before_and_after_in_bitacora(): void
    {
        $producto = $this->productos->create(['name' => 'Silla', 'precio' => 450, 'created_at' => now()]);

        $response = $this->authenticated()->putJson("/api/v1/productos/{$producto['id']}", [
            'name' => 'Silla Ergonómica',
            'precio' => 550,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.name', 'Silla Ergonómica');

        $entry = $this->bitacora->entries[0];
        $this->assertSame('producto', $entry['entidad']);
        $this->assertSame('edicion', $entry['tipo']);
        $this->assertSame('Silla', $entry['datos_anteriores']['name']);
        $this->assertSame('Silla Ergonómica', $entry['datos_nuevos']['name']);
    }

    public function test_destroy_removes_producto(): void
    {
        $producto = $this->productos->create(['name' => 'Mousepad', 'precio' => 99, 'created_at' => now()]);

        $this->authenticated()->deleteJson("/api/v1/productos/{$producto['id']}")->assertStatus(200);

        $this->assertNull($this->productos->find($producto['id']));
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $this->authenticated()->getJson('/api/v1/productos/no-existe')->assertStatus(404);
    }
}
