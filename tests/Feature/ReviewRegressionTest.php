<?php

/*
 * Regression tests for the first independent code and security review
 * (2026-09-22). Each test is named after the finding it pins down.
 */

use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use RomanSulzhyk\FilamentImport\Importing\FillableImporter;
use RomanSulzhyk\FilamentImport\Importing\ImportRunner;
use RomanSulzhyk\FilamentImport\Importing\RowFailure;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use RomanSulzhyk\FilamentImport\Reading\CsvReader;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Reading\UnsupportedSpreadsheet;
use RomanSulzhyk\FilamentImport\Reading\XlsxReader;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

$identity = ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'city' => 'city', 'joined_at' => 'joined_at'];

it('B1: imports the value Excel cached for a formula, not the formula text', function () {
    $path = Files::xlsxWithFormulas(
        [['name', 'email', 'phone', 'city'], ['PLACEHOLDER', 'ada@example.com', 'PLACEHOLDER', 'PLACEHOLDER']],
        [
            'A2' => ['formula' => '"Ada"&" "&"Lovelace"', 'value' => 'Ada Lovelace', 'type' => 'str'],
            'C2' => ['formula' => '5550000+101', 'value' => '5550101'],
            'D2' => ['formula' => 'UPPER("london")', 'value' => 'LONDON', 'type' => 'str'],
        ],
    );

    $row = iterator_to_array(ReaderFactory::make($path)->rows(), false)[0];

    expect($row['name'])->toBe('Ada Lovelace')
        ->and($row['phone'])->toBe('5550101')
        ->and($row['city'])->toBe('LONDON');
});

it('B1: still reads text that merely starts with "=" as text', function () {
    $path = FailedRowsWriter::write(['name'], [new RowFailure(2, ['name' => '=not a formula'], ['x'])]);
    $local = tempnam(sys_get_temp_dir(), 'r') . '.xlsx';
    file_put_contents($local, Storage::disk(FailedRowsWriter::disk())->get($path));

    expect(iterator_to_array(ReaderFactory::make($local)->rows(), false)[0]['name'])->toBe('=not a formula');
});

it('B2: detects UTF-8 correctly when the sample boundary splits a multibyte character', function () {
    $rows = [['name', 'email', 'city']];

    foreach (range(1, 700) as $i) {
        $rows[] = ["Особа {$i}", "p{$i}@example.com", 'Київ-Святошинський район'];
    }

    // Shift the content one byte at a time so byte 65,536 lands inside a
    // two-byte Cyrillic character at least once.
    foreach (range(0, 5) as $shift) {
        $rows[1][0] = 'Особа ' . str_repeat('x', $shift);
        $path = Files::csv($rows);

        expect(CsvReader::detectEncoding($path))->toBe('UTF-8');
    }

    $result = (new ImportRunner(ReaderFactory::make($path), fn ($import, $map, $options) => (new FillableImporter($import, $map, $options))->using(Customer::class, FillableImporter::columnsFor(Customer::class)), ['name' => 'name', 'email' => 'email', 'city' => 'city']))->run();

    expect($result->created)->toBe(700)
        ->and(Customer::firstWhere('email', 'p700@example.com')->city)->toBe('Київ-Святошинський район');
});

it('M2: prunes failed-rows reports older than their lifetime', function () {
    config(['filament-import.failed_rows_ttl_minutes' => 60]);
    $disk = Storage::disk(FailedRowsWriter::disk());

    $old = FailedRowsWriter::write(['name'], [new RowFailure(2, ['name' => 'x'], ['bad'])]);
    touch($disk->path($old), now()->subHours(2)->getTimestamp());

    $fresh = FailedRowsWriter::write(['name'], [new RowFailure(2, ['name' => 'y'], ['bad'])]);

    expect($disk->exists($old))->toBeFalse()
        ->and($disk->exists($fresh))->toBeTrue();

    touch($disk->path($fresh), now()->subHours(2)->getTimestamp());
    $this->artisan('filament-import:prune')->assertSuccessful();

    expect($disk->exists($fresh))->toBeFalse();
});

it('M3: a value starting with + or - survives the fix-and-re-upload round trip unchanged', function () {
    $path = FailedRowsWriter::write(['name', 'email', 'phone', 'city'], [
        new RowFailure(2, ['name' => 'Ada', 'email' => 'broken', 'phone' => '+15550101', 'city' => '-'], ['bad email']),
    ]);
    $local = tempnam(sys_get_temp_dir(), 'r') . '.xlsx';
    file_put_contents($local, Storage::disk(FailedRowsWriter::disk())->get($path));

    $row = iterator_to_array(ReaderFactory::make($local)->rows(), false)[0];

    expect($row['phone'])->toBe('+15550101')
        ->and($row['city'])->toBe('-');
});

it('M4: a required rule and an upsert key must be mapped, so they cannot be skipped', function () {
    $columns = collect(FillableImporter::columnsFor(Customer::class, ['city' => 'required'], ['email']))
        ->keyBy(fn (ImportColumn $column) => $column->getName());

    expect($columns['city']->isMappingRequired())->toBeTrue()
        ->and($columns['email']->isMappingRequired())->toBeTrue()
        ->and($columns['phone']->isMappingRequired())->toBeFalse();
});

it('M5: can narrow or exclude zero-config columns, and refuses a model with nothing fillable', function () {
    $names = fn (array $columns) => array_map(fn ($c) => $c->getName(), $columns);

    expect($names(FillableImporter::columnsFor(Customer::class, except: ['phone', 'joined_at'])))->toBe(['name', 'email', 'city'])
        ->and($names(FillableImporter::columnsFor(Customer::class, only: ['name', 'email', 'not_fillable'])))->toBe(['name', 'email']);

    $guarded = new class extends Model
    {
        protected $guarded = [];
    };

    expect(fn () => FillableImporter::columnsFor($guarded::class))->toThrow(LogicException::class);
});

it('M6: refuses an xlsx whose contents would expand far beyond its size', function () {
    $path = tempnam(sys_get_temp_dir(), 'bomb') . '.xlsx';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);
    $zip->addFromString('xl/sharedStrings.xml', str_repeat('A', 20 * 1024 * 1024));
    $zip->close();

    expect(fn () => ReaderFactory::make($path))->toThrow(UnsupportedSpreadsheet::class);
});

it('A1: does not auto-select headers that only contain a column name', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['Username', 'Last Name', 'Email Opt-in', 'Phone Extension', 'Billing City'],
        array_map(fn ($name) => ImportColumn::make($name), ['name', 'email', 'phone', 'city']),
    );

    expect($map)->toBe(['name' => null, 'email' => null, 'phone' => null, 'city' => null]);
});

it('A1: still matches owner prefixes, noise suffixes and typos', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['Customer Email', 'Phone Number', 'Adress'],
        array_map(fn ($name) => ImportColumn::make($name), ['email', 'phone', 'address']),
    );

    expect($map)->toBe(['email' => 'Customer Email', 'phone' => 'Phone Number', 'address' => 'Adress']);
});

it('A2: a real column named __row keeps its data', function () use ($identity) {
    $row = iterator_to_array(ReaderFactory::make(Files::csv([['__row', 'name'], ['7', 'Ada']]))->rows(), false)[0];

    expect($row)->toBe(['__row' => '7', 'name' => 'Ada']);
});

it('A3: line numbers stay correct after a physical blank line', function () use ($identity) {
    $path = tempnam(sys_get_temp_dir(), 'blank') . '.csv';
    file_put_contents($path, "name,email,phone,city,joined_at\nAda,ada@example.com,,,\n\nBroken,nope,,,\n");

    $result = (new ImportRunner(ReaderFactory::make($path), CustomerImporter::class, $identity))->run();

    expect($result->failures[0]->row)->toBe(4);
});

it('A8: refuses a legacy .xls file with a helpful message instead of reading garbage', function () {
    $path = tempnam(sys_get_temp_dir(), 'legacy') . '.xls';
    file_put_contents($path, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\0", 512));

    expect(fn () => ReaderFactory::make($path, 'old.xls'))
        ->toThrow(UnsupportedSpreadsheet::class, 'save it as .xlsx');
});

it('A11: formats Excel durations of a day or more without dropping the days', function () {
    expect(XlsxReader::normalize(new DateInterval('P1DT2H3M4S')))->toBe('26:03:04');
});
