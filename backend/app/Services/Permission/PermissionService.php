<?php

namespace App\Services\Permission;

use App\DTOs\PerfilDTO;
use App\Enums\SeccionSistema;
use App\Interfaces\PerfilRepositoryInterface;

class PermissionService
{
    public function __construct(private readonly PerfilRepositoryInterface $perfiles)
    {
    }

    public function aggregate(array $perfilIds): array
    {
        $perfilDtos = array_map(
            fn (array $perfil) => PerfilDTO::fromArray($perfil),
            array_values(array_filter(array_map(
                fn (string $perfilId) => $this->perfiles->find($perfilId),
                $perfilIds
            )))
        );

        $result = [];

        foreach (SeccionSistema::values() as $seccion) {
            $result[$seccion] = [
                'seccion' => $seccion,
                'crear' => false,
                'consultar' => false,
                'editar' => false,
                'eliminar' => false,
            ];
        }

        foreach ($perfilDtos as $perfil) {
            foreach ($perfil->secciones as $entry) {
                $seccion = $entry['seccion'] ?? null;

                if ($seccion === null || ! isset($result[$seccion])) {
                    continue;
                }

                foreach (['crear', 'consultar', 'editar', 'eliminar'] as $permiso) {
                    $result[$seccion][$permiso] = $result[$seccion][$permiso] || (bool) ($entry[$permiso] ?? false);
                }
            }
        }

        return array_values($result);
    }

    public function puede(array $perfilIds, string $seccion, string $permiso): bool
    {
        foreach ($this->aggregate($perfilIds) as $entry) {
            if ($entry['seccion'] === $seccion) {
                return (bool) ($entry[$permiso] ?? false);
            }
        }

        return false;
    }
}
