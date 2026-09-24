<?php

use RomanSulzhyk\FilamentImport\Reading\XlsxReader;

it('turns Excel cell values into the strings a CSV importer expects', function (mixed $cell, string $expected) {
    expect(XlsxReader::normalize($cell))->toBe($expected);
})->with([
    'integer phone' => [5550101, '5550101'],
    'integral float' => [42.0, '42'],
    'decimal' => [12.5, '12.5'],
    'money' => [1999.99, '1999.99'],
    'long id kept whole' => [1234567890123.0, '1234567890123'],
    'tiny value, no scientific notation' => [0.00001, '0.00001'],
    'boolean true' => [true, 'true'],
    'boolean false' => [false, 'false'],
    'date' => [new DateTimeImmutable('2024-03-05'), '2024-03-05'],
    'datetime' => [new DateTimeImmutable('2024-03-05 14:30:00'), '2024-03-05 14:30:00'],
    'trimmed text' => ['  London ', 'London'],
]);

it('leaves empty cells empty rather than inventing a value', function () {
    expect(XlsxReader::normalize(null))->toBeNull();
});
