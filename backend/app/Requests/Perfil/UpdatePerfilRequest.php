<?php

namespace App\Requests\Perfil;

use App\Enums\SeccionSistema;
use App\Interfaces\PerfilRepositoryInterface;
use App\Requests\BaseFormRequest;
use App\Rules\FirestoreUnique;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdatePerfilRequest',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Administrador'),
        new OA\Property(
            property: 'secciones',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'seccion', type: 'string', example: 'usuarios'),
                    new OA\Property(property: 'crear', type: 'boolean', example: true),
                    new OA\Property(property: 'consultar', type: 'boolean', example: true),
                    new OA\Property(property: 'editar', type: 'boolean', example: true),
                    new OA\Property(property: 'eliminar', type: 'boolean', example: false),
                ]
            )
        ),
    ]
)]
class UpdatePerfilRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new FirestoreUnique(
                    fn (string $name) => app(PerfilRepositoryInterface::class)->findByNombre($name),
                    $this->route('perfil')
                ),
            ],
            'secciones' => ['nullable', 'array'],
            'secciones.*.seccion' => ['required', Rule::in(SeccionSistema::values())],
            'secciones.*.crear' => ['boolean'],
            'secciones.*.consultar' => ['boolean'],
            'secciones.*.editar' => ['boolean'],
            'secciones.*.eliminar' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del perfil es obligatorio.',
            'secciones.*.seccion.required' => 'La sección es obligatoria.',
            'secciones.*.seccion.in' => 'La sección seleccionada no es válida.',
        ];
    }
}
