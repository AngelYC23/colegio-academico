<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Checks format only. Mailbox ownership requires an actual verification email. */
class AcademicEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || substr_count($value, '@') !== 1 || strlen($value) > 254 || preg_match('/\s/u', $value)) {
            $fail('Ingrese un correo con un solo @ y un dominio completo, por ejemplo nombre@colegio.edu.gt.');
            return;
        }
        [, $domain] = explode('@', $value, 2);
        $domainPattern = '/\A(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]{2,59})\z/i';
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false || !preg_match($domainPattern, $domain)) {
            $fail('Ingrese un correo válido, sin espacios y con un dominio completo como colegio.edu.gt.');
        }
    }
}
