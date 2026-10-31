<?php

namespace App\Support;

use App\Rules\AcademicEmail;
use App\Rules\ContactPhone;
use Illuminate\Http\Request;

class InputRules
{
    public const OPTIONAL = ['birth_date', 'sex', 'address', 'phone', 'email', 'user_id', 'description'];

    public static function email(): array
    {
        return ['bail', 'string', 'max:254', new AcademicEmail];
    }

    public static function personName(): array
    {
        // Unicode letters include accents and non-Latin names; punctuation supports O’Neill and initials.
        return ['bail', 'string', 'max:100', "regex:/\\A\\p{L}[\\p{L}\\p{M}]*(?:[ .'\\x{2019}-]+\\p{L}[\\p{L}\\p{M}]*)*\\.?\\z/u"];
    }

    public static function username(): array
    {
        return ['bail', 'string', 'min:3', 'max:50', 'regex:/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/'];
    }

    public static function phone(): array
    {
        return ['bail', 'string', 'max:30', new ContactPhone];
    }

    public static function normalize(Request $request, array $fields): void
    {
        $updates = [];
        foreach ($fields as $field) {
            $value = $request->input($field);
            if (!is_string($value)) continue;
            $value = trim($value);
            if (in_array($field, ['name', 'first_name', 'last_name'])) {
                $value = preg_replace('/[ \t]+/u', ' ', $value);
            }
            if ($field === 'email') $value = mb_strtolower($value);
            $updates[$field] = $value === '' ? null : $value;
        }
        $request->merge($updates);
    }
}
