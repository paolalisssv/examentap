<?php

namespace App\Requests\Auth;

use App\Requests\BaseFormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ForgotPasswordRequest',
    type: 'object',
    required: ['email'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.perez@example.com'),
    ]
)]
class ForgotPasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
        ];
    }
}
