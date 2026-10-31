<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContactPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/\A\+?[0-9 ()-]+\z/', $value)) {
            $fail('El teléfono solo admite números, un + inicial, espacios, guiones y paréntesis.');
            return;
        }
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) < 8 || strlen($digits) > 15 || preg_match('/\A([0-9])\1+\z/', $digits)
            || !preg_match('/\A[^()]*(?:\([0-9 ]+\)[^()]*)?\z/', $value)) {
            $fail('Ingrese un teléfono de 8 a 15 dígitos; por ejemplo 5555-1234 o +502 5555-1234.');
        }
    }
}
