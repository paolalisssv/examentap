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

class PerfilExportTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $usuarios = new InMemoryUserRepository();
        $perfiles = new InMemoryPerfilRepository();

        $perfiles->create([
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
        $this->app->instance(PerfilRepositoryInterface::class, $perfiles);
        $this->app->instance(BitacoraRepositoryInterface::class, new InMemoryBitacoraRepository());
        $this->app->instance(TokenRepositoryInterface::class, new InMemoryTokenRepository());
        $this->app->instance(FileStorageInterface::class, new FakeFileStorage());

        $this->token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Secret123',
        ])->json('data.token');
    }

    public function test_export_pdf_returns_pdf_document(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get('/api/v1/perfiles/export/pdf');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_export_excel_returns_spreadsheet(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get('/api/v1/perfiles/export/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('Content-Type'));
    }
}
