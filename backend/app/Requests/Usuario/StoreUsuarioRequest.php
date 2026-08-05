<?php

namespace App\Requests\Usuario;

use App\Interfaces\UsuarioRepositoryInterface;
use App\Requests\BaseFormRequest;
use App\Rules\FirestoreUnique;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreUsuarioRequest',
    type: 'object',
    required: ['name', 'email', 'password', 'foto'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.perez@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123!'),
        new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+521234567890'),
        new OA\Property(property: 'foto', type: 'string', format: 'binary'),
        new OA\Property(property: 'perfiles', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
    ]
)]
class StoreUsuarioRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                new FirestoreUnique(fn (string $email) => app(UsuarioRepositoryInterface::class)->findByEmail($email)),
            ],
            'foto' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'telefono' => ['nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'perfiles' => ['nullable', 'array'],
            'perfiles.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'foto.required' => 'La foto de perfil es obligatoria.',
            'foto.image' => 'La foto debe ser una imagen válida.',
            'foto.mimes' => 'La foto debe tener formato jpg, jpeg, png o webp.',
            'foto.max' => 'La foto no debe superar los 2MB.',
            'password.required' => 'La contraseña es obligatoria.',
            'telefono.regex' => 'El teléfono no tiene un formato válido.',
        ];
    }
}
