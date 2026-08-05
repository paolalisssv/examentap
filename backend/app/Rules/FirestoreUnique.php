<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FirestoreUnique implements ValidationRule
{
    public function __construct(
        private readonly Closure $finder,
        private readonly ?string $ignoreId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existing = ($this->finder)($value);

        if ($existing === null) {
            return;
        }

        if ($this->ignoreId !== null && $existing['id'] === $this->ignoreId) {
            return;
        }

        $fail('El valor de :attribute ya está en uso.');
    }
}
