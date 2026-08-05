<?php

namespace App\Services\Usuario;

use App\DTOs\AuthenticatedUserDTO;
use App\DTOs\PerfilDTO;
use App\DTOs\UsuarioDTO;
use App\Enums\SeccionSistema;
use App\Exceptions\DomainException;
use App\Exceptions\NotFoundException;
use App\Interfaces\FileStorageInterface;
use App\Interfaces\PerfilRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;
use App\Services\Bitacora\BitacoraService;
use App\Services\Permission\PermissionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioService
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
        private readonly PerfilRepositoryInterface $perfiles,
        private readonly FileStorageInterface $storage,
        private readonly BitacoraService $bitacora,
        private readonly PermissionService $permissions,
    ) {
    }

    public function paginate(?string $search, string $sortField, string $sortDirection, int $page, int $perPage): array
    {
        $items = $this->usuarios->all();

        if ($search !== null && $search !== '') {
            $needle = mb_strtolower($search);
            $items = array_values(array_filter($items, function (array $usuario) use ($needle) {
                return str_contains(mb_strtolower((string) ($usuario['id'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($usuario['name'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($usuario['email'] ?? '')), $needle);
            }));
        }

        $sortField = in_array($sortField, ['id', 'name', 'email', 'created_at'], true) ? $sortField : 'created_at';
        $direction = strtolower($sortDirection) === 'asc' ? 1 : -1;

        usort($items, function (array $a, array $b) use ($sortField, $direction) {
            $valueA = $a[$sortField] ?? null;
            $valueB = $b[$sortField] ?? null;

            if ($valueA instanceof Carbon && $valueB instanceof Carbon) {
                return $direction * ($valueA->timestamp <=> $valueB->timestamp);
            }

            return $direction * strcasecmp((string) $valueA, (string) $valueB);
        });

        $total = count($items);
        $paged = array_slice($items, ($page - 1) * $perPage, $perPage);

        return [
            'items' => array_map(fn (array $usuario) => UsuarioDTO::fromArray($usuario), $paged),
            'total' => $total,
        ];
    }

    public function find(string $id): UsuarioDTO
    {
        $usuario = $this->usuarios->find($id);

        if ($usuario === null) {
            throw new NotFoundException('Usuario no encontrado.');
        }

        return UsuarioDTO::fromArray($usuario);
    }

    public function detail(string $id): array
    {
        $usuario = $this->find($id);

        $perfiles = array_values(array_filter(array_map(
            fn (string $perfilId) => $this->perfiles->find($perfilId),
            $usuario->perfiles
        )));

        $perfilDtos = array_map(fn (array $perfil) => PerfilDTO::fromArray($perfil), $perfiles);

        return [
            'usuario' => $usuario,
            'perfiles' => $perfilDtos,
            'secciones' => $this->permissions->aggregate($usuario->perfiles),
        ];
    }

    public function create(array $data, UploadedFile $foto, ?AuthenticatedUserDTO $actor, bool $isBootstrap = false): UsuarioDTO
    {
        if ($isBootstrap) {
            $perfiles = [$this->ensureAdministradorPerfil()];
        } else {
            $perfiles = $data['perfiles'] ?? [];
            $this->assertPerfilesExist($perfiles);
        }

        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'foto_url' => $this->uploadFoto($foto),
            'perfiles' => $perfiles,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $created = $this->usuarios->create($fields);

        $actor ??= AuthenticatedUserDTO::fromArray($created);

        $this->bitacora->record('usuario', $created['id'], 'alta', null, $this->sanitize($created), $actor);

        return UsuarioDTO::fromArray($created);
    }

    public function update(string $id, array $data, ?UploadedFile $foto, AuthenticatedUserDTO $actor): UsuarioDTO
    {
        $before = $this->usuarios->find($id);

        if ($before === null) {
            throw new NotFoundException('Usuario no encontrado.');
        }

        $perfiles = $data['perfiles'] ?? [];
        $this->assertPerfilesExist($perfiles);

        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'perfiles' => $perfiles,
            'updated_at' => Carbon::now(),
        ];

        if ($foto !== null) {
            $fields['foto_url'] = $this->uploadFoto($foto);
        }

        if (! empty($data['password'])) {
            $fields['password'] = Hash::make($data['password']);
        }

        $updated = $this->usuarios->update($id, $fields);

        $this->bitacora->record('usuario', $id, 'edicion', $this->sanitize($before), $this->sanitize($updated), $actor);

        return UsuarioDTO::fromArray($updated);
    }

    public function delete(string $id): void
    {
        if ($this->usuarios->find($id) === null) {
            throw new NotFoundException('Usuario no encontrado.');
        }

        $this->usuarios->delete($id);
    }

    private function uploadFoto(UploadedFile $foto): string
    {
        return $this->storage->upload($foto, 'usuarios/'.Str::uuid().'.'.$foto->getClientOriginalExtension());
    }

    private function assertPerfilesExist(array $perfiles): void
    {
        foreach ($perfiles as $perfilId) {
            if ($this->perfiles->find((string) $perfilId) === null) {
                throw new DomainException("El perfil {$perfilId} no existe.");
            }
        }
    }

    private function sanitize(array $fields): array
    {
        unset($fields['password']);

        return $fields;
    }

    private function ensureAdministradorPerfil(): string
    {
        $existing = $this->perfiles->findByNombre('Administrador');

        if ($existing !== null) {
            return $existing['id'];
        }

        $secciones = array_map(fn (string $seccion) => [
            'seccion' => $seccion,
            'crear' => true,
            'consultar' => true,
            'editar' => true,
            'eliminar' => true,
        ], SeccionSistema::values());

        $created = $this->perfiles->create([
            'name' => 'Administrador',
            'secciones' => $secciones,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $created['id'];
    }
}
