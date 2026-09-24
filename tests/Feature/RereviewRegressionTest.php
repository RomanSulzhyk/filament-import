<?php

/*
 * Regression tests for the second independent review (2026-09-22).
 * Each test is named after the finding it pins down.
 */

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use RomanSulzhyk\FilamentImport\Importing\RowFailure;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Reading\UnsupportedSpreadsheet;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListTeamCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Team;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;

/** An xlsx whose shared strings really expand to about $n * 50 bytes. */
function rrBomb(int $n): string
{
    $path = Files::xlsx([['name', 'email'], ['Ada', 'ada@example.com']]);
    $zip = new ZipArchive;
    $zip->open($path);
    $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $n . '" uniqueCount="' . $n . '">' . str_repeat('<si><t>aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</t></si>', $n) . '</sst>');
    $zip->close();
    clearstatcache();

    return $path;
}

/** Rewrites the declared uncompressed size of one entry, in the central directory and local header. */
function rrLieAboutSize(string $path, string $entry, int $fakeSize): void
{
    $bin = file_get_contents($path);
    $pos = 0;

    while (($pos = strpos($bin, "PK\x01\x02", $pos)) !== false) {
        $nameLength = unpack('v', substr($bin, $pos + 28, 2))[1];

        if (substr($bin, $pos + 46, $nameLength) === $entry) {
            $local = unpack('V', substr($bin, $pos + 42, 4))[1];
            $bin = substr_replace($bin, pack('V', $fakeSize), $pos + 24, 4);

            if (unpack('V', substr($bin, $local + 22, 4))[1] !== 0) {
                $bin = substr_replace($bin, pack('V', $fakeSize), $local + 22, 4);
            }
        }

        $pos += 4;
    }

    file_put_contents($path, $bin);
    clearstatcache();
}

it('R-2: refuses an xlsx that lies about its uncompressed size', function () {
    config()->set('filament-import.xlsx_max_uncompressed_bytes', 5 * 1024 * 1024);

    $path = rrBomb(200_000); // about 10 MB of real XML
    rrLieAboutSize($path, 'xl/sharedStrings.xml', 2_000);

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->statName('xl/sharedStrings.xml')['size'])->toBe(2_000);
    $zip->close();

    expect(fn () => ReaderFactory::make($path))->toThrow(UnsupportedSpreadsheet::class);
});

it('R-2: measures the ratio against the real file size', function () {
    config()->set('filament-import.xlsx_max_compression_ratio', 20);

    expect(fn () => ReaderFactory::make(rrBomb(40_000)))->toThrow(UnsupportedSpreadsheet::class);
});

it('R-2: still accepts an ordinary spreadsheet', function () {
    $rows = [['name', 'email']];

    for ($i = 1; $i <= 2000; $i++) {
        $rows[] = ["Customer {$i}", "customer{$i}@example.com"];
    }

    expect(ReaderFactory::make(Files::xlsx($rows))->headers())->toBe(['name', 'email']);
});

it('R-3: refuses a different model on a tenant-scoped resource in zero-config mode, with a clear message', function () {
    $team = Team::create(['name' => 'Acme']);
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));

    Filament::setCurrentPanel(Filament::getPanel('tenant'));
    Filament::setTenant($team);

    $action = ExcelImportAction::make()->importModel(Customer::class);
    $action->livewire(new ListTeamCustomers);

    expect(fn () => $action->getImportColumns())->toThrow(LogicException::class, 'cannot keep those rows inside the current tenant');

    $action = ExcelImportAction::make()->importModel(Customer::class)->importColumns(['name', 'email']);
    $action->livewire(new ListTeamCustomers);

    expect(array_map(fn ($column) => $column->getName(), $action->getImportColumns()))->toBe(['name', 'email']);
});

it('R-4: reads a CSV that starts with blank lines, keeping file line numbers', function () {
    $path = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($path, "\n\nname,email\nAda,ada@example.com\n");

    $reader = ReaderFactory::make($path, 'leading.csv');

    expect($reader->headers())->toBe(['name', 'email'])
        ->and(iterator_to_array($reader->rows()))->toBe([4 => ['name' => 'Ada', 'email' => 'ada@example.com']]);
});

it('R-5: a failing prune is reported and does not lose the report', function () {
    Exceptions::fake();

    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('files')->andThrow(new RuntimeException('ListBucket denied'));
    $disk->shouldReceive('put')->once()->andReturn(true);
    Storage::shouldReceive('disk')->andReturn($disk);

    $path = FailedRowsWriter::write(['name'], [new RowFailure(2, ['name' => 'x'], ['Bad'])]);

    expect($path)->toEndWith('.xlsx');
    Exceptions::assertReported(RuntimeException::class);
});
