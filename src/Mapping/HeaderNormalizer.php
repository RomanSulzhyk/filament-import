<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

use Illuminate\Support\Str;

class HeaderNormalizer
{
    /**
     * "E-mail Address", "e_mail address " and "EMAIL-ADDRESS" all become
     * "e mail address". Accented Latin, Cyrillic and Greek are transliterated,
     * so "Телефон" and "telefon" compare equal. Every other script (Arabic,
     * Hebrew, Devanagari, CJK, Thai and so on) is kept as written, with its
     * combining marks: transliterating it loses vowels, so unrelated words
     * would compare equal ("नाम" would become "NaMa", "جوال" the English
     * "goal"). With ext-intl, full-width and half-width forms are folded first
     * (NFKC), so "ＥＭＡＩＬ" and "ﾒｰﾙ" meet "EMAIL" and "メール". Both sides of
     * every comparison go through this same function.
     */
    public static function normalize(string $value): string
    {
        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_KC) ?: $value;
        }

        $value = Str::of($value)->replaceMatches('/([a-z])([A-Z])/', '$1 $2')->lower()->toString();
        $ascii = Str::ascii($value);

        $otherScript = preg_match('/[^\p{Latin}\p{Cyrillic}\p{Greek}\P{L}]/u', $value) === 1
            || static::losesLetters($value);

        if (! $otherScript && trim(preg_replace('/[^a-z0-9]+/', '', $ascii)) !== '') {
            $value = $ascii;
        }

        $value = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Whether transliteration would drop a letter: Tajik "ҳ" and "ӯ" have no
     * Latin form, so "Шаҳр" would become "sar" and meet a SAR currency column.
     */
    protected static function losesLetters(string $value): bool
    {
        preg_match_all('/[^\x00-\x7F]/u', $value, $chars);

        foreach (array_unique($chars[0]) as $char) {
            if (preg_match('/\p{L}/u', $char) === 1 && preg_replace('/[^a-z0-9]/i', '', Str::ascii($char)) === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * The parts of a bilingual header such as "Email (メール)" or
     * "Name / 名前": the Latin, Cyrillic and Greek part, and the part in any
     * other script. Empty when the header is written in one of them only.
     *
     * @return list<string>
     */
    public static function scriptParts(string $value): array
    {
        $transliterable = preg_replace('/[^\p{Latin}\p{Cyrillic}\p{Greek}\P{L}]/u', ' ', $value);
        $other = preg_replace('/[\p{Latin}\p{Cyrillic}\p{Greek}]/u', ' ', $value);

        if (preg_match('/\p{L}/u', $transliterable) !== 1 || preg_match('/\p{L}/u', $other) !== 1) {
            return [];
        }

        return [$transliterable, $other];
    }

    public static function compact(string $value): string
    {
        return str_replace(' ', '', static::normalize($value));
    }
}
