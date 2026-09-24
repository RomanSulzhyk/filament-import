<?php

/*
 * Behaviour added before the first public release, from the local demo run of
 * 2026-09-24 and the two findings the re-review left partial (A2, A11).
 */

use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use RomanSulzhyk\FilamentImport\Reading\CsvReader;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use Filament\Actions\Imports\ImportColumn;

function preCsv(string $content, string $name = 'file.csv'): string
{
    $path = tempnam(sys_get_temp_dir(), 'pre') . '-' . $name;
    file_put_contents($path, $content);

    return $path;
}

it('A2: keeps a trailing column that has data but no header', function () {
    $reader = ReaderFactory::make(preCsv("name,email\nAda,ada@example.com,VIP\n"));

    expect($reader->headers())->toBe(['name', 'email', 'column_3'])
        ->and(iterator_to_array($reader->rows()))->toBe([2 => ['name' => 'Ada', 'email' => 'ada@example.com', 'column_3' => 'VIP']]);
});

it('A2: still drops trailing columns that are empty', function () {
    $reader = ReaderFactory::make(Files::xlsx([['name', 'email', '', ''], ['Ada', 'ada@example.com', '', '']]));

    expect($reader->headers())->toBe(['name', 'email']);
});

it('reads an xlsx whose first rows are blank, keeping sheet line numbers', function () {
    $reader = ReaderFactory::make(Files::xlsx([[''], [''], ['name', 'email'], ['Ada', 'ada@example.com']]));

    expect($reader->headers())->toBe(['name', 'email'])
        ->and(array_keys(iterator_to_array($reader->rows())))->toBe([4]);
});

it('A11: tells Windows-1252 from Windows-1251', function (string $text, string $encoding) {
    $path = preCsv(mb_convert_encoding("name,city\nAda,{$text}\n", $encoding, 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe($encoding)
        ->and(iterator_to_array((new CsvReader($path))->rows(), false)[0]['city'])->toBe($text);
})->with([
    'French' => ['Hôtel ÆØÅ Øresund', 'Windows-1252'],
    'German' => ['Größe Müller', 'Windows-1252'],
    'Spanish' => ['São Paulo Ñandú', 'Windows-1252'],
    'Ukrainian' => ['Тарас Шевченко з Києва', 'Windows-1251'],
    'Russian' => ['Иван Петров из Москвы', 'Windows-1251'],
    'Short Cyrillic' => ['Ян Ли', 'Windows-1251'],
]);

it('matches whole headers written in other languages', function () {
    $columns = [
        ImportColumn::make('name'),
        ImportColumn::make('email'),
        ImportColumn::make('phone'),
        ImportColumn::make('city'),
        ImportColumn::make('country'),
    ];

    $map = (new HeuristicColumnMatcher)->match(["Ім'я", 'Ел. пошта', 'Телефон', 'Місто', 'Країна'], $columns);

    expect($map)->toBe(['name' => "Ім'я", 'email' => 'Ел. пошта', 'phone' => 'Телефон', 'city' => 'Місто', 'country' => 'Країна']);

    $map = (new HeuristicColumnMatcher)->match(['Nombre', 'Correo electrónico', 'Teléfono', 'Stadt', 'Land'], $columns);

    expect($map)->toBe(['name' => 'Nombre', 'email' => 'Correo electrónico', 'phone' => 'Teléfono', 'city' => 'Stadt', 'country' => 'Land']);
});

it('never matches a foreign header by containment', function () {
    $map = (new HeuristicColumnMatcher)->match(['Місто доставки', 'Telefon privat'], [ImportColumn::make('city'), ImportColumn::make('phone')]);

    expect($map)->toBe(['city' => null, 'phone' => null]);
});

it('prefers an exact English header over a synonym', function () {
    $map = (new HeuristicColumnMatcher)->match(['Місто', 'City'], [ImportColumn::make('city')]);

    expect($map)->toBe(['city' => 'City']);
});
