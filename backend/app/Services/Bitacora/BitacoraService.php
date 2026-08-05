<?php

namespace App\Services\Bitacora;

use App\DTOs\AuthenticatedUserDTO;
use App\Interfaces\BitacoraRepositoryInterface;
use Illuminate\Support\Carbon;

class BitacoraService
{
    public function __construct(private readonly BitacoraRepositoryInterface $bitacora)
    {
    }

    public function record(string $entidad, string $entidadId, string $tipo, ?array $antes, array $despues, AuthenticatedUserDTO $actor): void
    {
        $this->bitacora->create([
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'tipo' => $tipo,
            'datos_anteriores' => $antes,
            'datos_nuevos' => $despues,
            'realizado_por' => [
                'id' => $actor->id,
                'email' => $actor->email,
            ],
            'created_at' => Carbon::now(),
        ]);
    }
}
