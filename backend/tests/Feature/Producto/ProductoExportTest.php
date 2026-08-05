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

class ProductoExportTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $usuarios = new InMemoryUserRepository();
        $usuarios->seed('user-1', 'Usuario', 'usuario@example.com', Hash::make('Secret123'));

        $this->app->instance(UserRepositoryInterface::class, $usuarios);
        $this->app->instance(UsuarioRepositoryInterface::class, $usuarios);
        $this->app->instance(PerfilRepositoryInterface::class, new InMemoryPerfilRepository());
        $this->app->instance(ProductoRepositoryInterface::class, new InMemoryProductoRepository());
        $this->app->instance(BitacoraRepositoryInterface::class, new InMemoryBitacoraRepository());
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());

        $this->token = $this->postJson('/api/v1/auth/login', [
            'email' => 'usuario@example.com',
            'password' => 'Secret123',
        ])->json('data.token');
    }

    public function test_export_pdf_returns_pdf_document(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get('/api/v1/productos/export/pdf');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_export_excel_returns_spreadsheet(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get('/api/v1/productos/export/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('Content-Type'));
    }
}
