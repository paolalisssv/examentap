<?php

namespace App\Services\Producto;

use App\DTOs\AuthenticatedUserDTO;
use App\DTOs\ProductoDTO;
use App\Exceptions\NotFoundException;
use App\Interfaces\ProductoRepositoryInterface;
use App\Services\Bitacora\BitacoraService;
use Illuminate\Support\Carbon;

class ProductoService
{
    public function __construct(
        private readonly ProductoRepositoryInterface $productos,
        private readonly BitacoraService $bitacora,
    ) {
    }

    // Firestore no soporta búsqueda ni orden dinámico del lado del servidor para este
    // caso, así que se trae toda la colección y se filtra/ordena/pagina en memoria.
    public function paginate(?string $search, string $sortField, string $sortDirection, int $page, int $perPage): array
    {
        $items = $this->productos->all();

        if ($search !== null && $search !== '') {
            $needle = mb_strtolower($search);
            $items = array_values(array_filter($items, function (array $producto) use ($needle) {
                return str_contains(mb_strtolower((string) ($producto['id'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($producto['name'] ?? '')), $needle);
            }));
        }

        $sortField = in_array($sortField, ['id', 'name', 'precio', 'created_at'], true) ? $sortField : 'created_at';
        $direction = strtolower($sortDirection) === 'asc' ? 1 : -1;

        usort($items, function (array $a, array $b) use ($sortField, $direction) {
            $valueA = $a[$sortField] ?? null;
            $valueB = $b[$sortField] ?? null;

            if ($valueA instanceof Carbon && $valueB instanceof Carbon) {
                return $direction * ($valueA->timestamp <=> $valueB->timestamp);
            }

            if (is_numeric($valueA) && is_numeric($valueB)) {
                return $direction * ((float) $valueA <=> (float) $valueB);
            }

            return $direction * strcasecmp((string) $valueA, (string) $valueB);
        });

        $total = count($items);
        $paged = array_slice($items, ($page - 1) * $perPage, $perPage);

        return [
            'items' => array_map(fn (array $producto) => ProductoDTO::fromArray($producto), $paged),
            'total' => $total,
        ];
    }

    public function find(string $id): ProductoDTO
    {
        $producto = $this->productos->find($id);

        if ($producto === null) {
            throw new NotFoundException('Producto no encontrado.');
        }

        return ProductoDTO::fromArray($producto);
    }

    public function create(array $data, AuthenticatedUserDTO $actor): ProductoDTO
    {
        $fields = [
            'name' => $data['name'],
            'precio' => (float) $data['precio'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $created = $this->productos->create($fields);

        $this->bitacora->record('producto', $created['id'], 'alta', null, $created, $actor);

        return ProductoDTO::fromArray($created);
    }

    public function update(string $id, array $data, AuthenticatedUserDTO $actor): ProductoDTO
    {
        $before = $this->productos->find($id);

        if ($before === null) {
            throw new NotFoundException('Producto no encontrado.');
        }

        $fields = [
            'name' => $data['name'],
            'precio' => (float) $data['precio'],
            'updated_at' => Carbon::now(),
        ];

        $updated = $this->productos->update($id, $fields);

        $this->bitacora->record('producto', $id, 'edicion', $before, $updated, $actor);

        return ProductoDTO::fromArray($updated);
    }

    public function delete(string $id): void
    {
        if ($this->productos->find($id) === null) {
            throw new NotFoundException('Producto no encontrado.');
        }

        $this->productos->delete($id);
    }
}
