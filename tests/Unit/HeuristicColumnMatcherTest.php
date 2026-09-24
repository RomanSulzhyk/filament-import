<?php

use RomanSulzhyk\FilamentImport\Mapping\HeaderNormalizer;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use Filament\Actions\Imports\ImportColumn;

function columns(string ...$names): array
{
    return array_map(fn ($name) => ImportColumn::make($name), $names);
}

it('normalizes headers written in different styles to the same form', function () {
    expect(HeaderNormalizer::normalize('E-mail Address '))->toBe('e mail address')
        ->and(HeaderNormalizer::normalize('firstName'))->toBe('first name')
        ->and(HeaderNormalizer::normalize('FIRST_NAME'))->toBe('first name')
        ->and(HeaderNormalizer::normalize('Телефон'))->toBe(HeaderNormalizer::normalize('telefon'))
        ->and(HeaderNormalizer::normalize('电话号码'))->toBe('电话号码');
});

it('matches exact, compact and contained headers', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['FirstName', 'Customer Email', 'Zip Code', 'Notes'],
        columns('first_name', 'email', 'zip_code', 'phone'),
    );

    expect($map)->toBe([
        'first_name' => 'FirstName',
        'email' => 'Customer Email',
        'zip_code' => 'Zip Code',
        'phone' => null,
    ]);
});

it('uses the guesses declared on a core ImportColumn', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['Signup Date'],
        [ImportColumn::make('joined_at')->guess(['signup date'])],
    );

    expect($map)->toBe(['joined_at' => 'Signup Date']);
});

it('never feeds two columns from the same header, and the better fit wins', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['email'],
        columns('email', 'backup_email'),
    );

    expect($map)->toBe(['email' => 'email', 'backup_email' => null]);
});

it('ships every translation with the same keys as English', function () {
    $flatten = function (array $array, string $prefix = '') use (&$flatten): array {
        $keys = [];
        foreach ($array as $key => $value) {
            $keys = [...$keys, ...(is_array($value) ? $flatten($value, "{$prefix}{$key}.") : ["{$prefix}{$key}"])];
        }

        return $keys;
    };

    $english = $flatten(require __DIR__ . '/../../resources/lang/en/import.php');

    foreach (glob(__DIR__ . '/../../resources/lang/*/import.php') as $file) {
        expect($flatten(require $file))->toEqualCanonicalizing($english);
    }
});
