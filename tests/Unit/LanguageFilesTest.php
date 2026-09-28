<?php

use Illuminate\Support\Arr;
use RomanSulzhyk\FilamentImport\Mapping\HeaderNormalizer;
use RomanSulzhyk\FilamentImport\Mapping\HeaderSynonyms;

function langRoot(): string
{
    return dirname(__DIR__, 2).'/resources/lang';
}

/** @return list<string> */
function langLocales(): array
{
    return array_values(array_map('basename', glob(langRoot().'/*', GLOB_ONLYDIR)));
}

it('translates the interface into every locale Filament ships', function () {
    $filament = array_map('basename', glob(dirname(__DIR__, 2).'/vendor/filament/filament/resources/lang/*', GLOB_ONLYDIR));
    $missing = array_diff($filament, langLocales(), ['ru']);

    expect($missing)->toBe([]);
});

it('keeps every translation complete, with the same placeholders as English', function () {
    $english = Arr::dot(require langRoot().'/en/import.php');

    foreach (langLocales() as $locale) {
        $strings = Arr::dot(require langRoot()."/{$locale}/import.php");

        expect(array_keys($strings))->toEqualCanonicalizing(array_keys($english), "{$locale} keys");

        foreach ($english as $key => $value) {
            preg_match_all('/:[a-z_]+/', $value, $expected);

            // Laravel replaces a placeholder even when a suffix follows it,
            // which agglutinative languages use (Uzbek ":labelni").
            foreach ($expected[0] as $placeholder) {
                expect(substr_count($strings[$key], $placeholder))
                    ->toBe(substr_count($value, $placeholder), "{$locale}.{$key} {$placeholder}");
            }

            expect(trim($strings[$key]))->not->toBe('', "{$locale}.{$key} is empty");
        }
    }
});

it('only uses known fields in the synonym files', function () {
    $allowed = [...array_keys(HeaderSynonyms::KEYS), 'given'];

    foreach (HeaderSynonyms::languageFiles() as $file) {
        expect(array_diff(array_keys(require $file), $allowed))->toBe([], basename($file));
    }
});

it('never lets one word or key point to two different fields', function () {
    $owner = [];

    foreach (HeaderSynonyms::KEYS as $field => $keys) {
        foreach ($keys as $key) {
            $owner[$key] = [$field, "key {$key}"];
        }
    }

    $clashes = [];

    foreach (HeaderSynonyms::languageFiles() as $file) {
        foreach (require $file as $field => $words) {
            // A first name may also head a full-name column; the matcher
            // resolves that from the file's other headers.
            $field = $field === 'given' ? 'first_name' : $field;

            foreach ($words as $word) {
                $compact = HeaderNormalizer::compact($word);

                if ($compact === '') {
                    $clashes[] = basename($file).": '{$word}' normalizes to nothing";

                    continue;
                }

                if (isset($owner[$compact]) && $owner[$compact][0] !== $field) {
                    $clashes[] = basename($file).": '{$word}' ({$field}) vs {$owner[$compact][1]} ({$owner[$compact][0]})";
                }

                $owner[$compact] ??= [$field, basename($file)." '{$word}'"];
            }
        }
    }

    expect($clashes)->toBe([]);
});

it('has no Russian files', function () {
    expect(is_dir(langRoot().'/ru'))->toBeFalse()
        ->and(file_exists(dirname(__DIR__, 2).'/resources/synonyms/ru.php'))->toBeFalse();
});
