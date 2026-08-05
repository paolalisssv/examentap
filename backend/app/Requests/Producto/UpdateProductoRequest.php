<?php

namespace App\Requests\Producto;

use App\Requests\BaseFormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateProductoRequest',
    type: 'object',
    required: ['name', 'precio'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Teclado mecánico'),
        new OA\Property(property: 'precio', type: 'number', format: 'float', example: 649.99),
    ]
)]
class UpdateProductoRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.min' => 'El nombre del producto debe tener al menos :min caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un valor numérico.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.regex' => 'El precio debe tener como máximo 3 dígitos enteros y 2 decimales.',
        ];
    }
}
