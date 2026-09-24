<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

use Illuminate\Support\Str;

class HeaderNormalizer
{
    /**
     * "E-mail Address", "e_mail address " and "EMAIL-ADDRESS" all become
     * "e mail address". Scripts with a Latin transliteration (Cyrillic, Greek,
     * accented Latin) are transliterated, so "Телефон" and "telefon" compare
     * equal. Scripts without one (CJK, Arabic) are kept as-is rather than
     * collapsing to an empty string. Both sides of every comparison go through
     * this same function, so the choice is consistent.
     */
    public static function normalize(string $value): string
    {
        $value = Str::of($value)->replaceMatches('/([a-z])([A-Z])/', '$1 $2')->lower()->toString();
        $ascii = Str::ascii($value);

        if (trim(preg_replace('/[^a-z0-9]+/', '', $ascii)) !== '') {
            $value = $ascii;
        }

        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    public static function compact(string $value): string
    {
        return str_replace(' ', '', static::normalize($value));
    }
}
