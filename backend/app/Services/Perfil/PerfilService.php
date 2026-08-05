<?php

namespace App\Services\Perfil;

use App\DTOs\PerfilDTO;
use App\Exceptions\NotFoundException;
use App\Interfaces\PerfilRepositoryInterface;
use Illuminate\Support\Carbon;

class PerfilService
{
    public function __construct(private readonly PerfilRepositoryInterface $perfiles)
    {
    }

    public function paginate(?string $search, string $sortField, string $sortDirection, int $page, int $perPage): array
    {
        $items = $this->perfiles->all();

        if ($search !== null && $search !== '') {
            $needle = mb_strtolower($search);
            $items = array_values(array_filter($items, function (array $perfil) use ($needle) {
                return str_contains(mb_strtolower((string) ($perfil['id'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($perfil['name'] ?? '')), $needle);
            }));
        }

        $sortField = in_array($sortField, ['id', 'name', 'created_at'], true) ? $sortField : 'created_at';
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
            'items' => array_map(fn (array $perfil) => PerfilDTO::fromArray($perfil), $paged),
            'total' => $total,
        ];
    }

    public function find(string $id): PerfilDTO
    {
        $perfil = $this->perfiles->find($id);

        if ($perfil === null) {
            throw new NotFoundException('Perfil no encontrado.');
        }

        return PerfilDTO::fromArray($perfil);
    }

    public function create(array $data): PerfilDTO
    {
        $fields = [
            'name' => $data['name'],
            'secciones' => $data['secciones'] ?? [],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return PerfilDTO::fromArray($this->perfiles->create($fields));
    }

    public function update(string $id, array $data): PerfilDTO
    {
        if ($this->perfiles->find($id) === null) {
            throw new NotFoundException('Perfil no encontrado.');
        }

        $fields = [
            'name' => $data['name'],
            'secciones' => $data['secciones'] ?? [],
            'updated_at' => Carbon::now(),
        ];

        return PerfilDTO::fromArray($this->perfiles->update($id, $fields));
    }

    public function delete(string $id): void
    {
        if ($this->perfiles->find($id) === null) {
            throw new NotFoundException('Perfil no encontrado.');
        }

        $this->perfiles->delete($id);
    }
}
