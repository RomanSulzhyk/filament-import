<?php

use Illuminate\Support\Facades\Storage;
use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use RomanSulzhyk\FilamentImport\Importing\RowFailure;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;

function reportReader(string $path)
{
    $local = tempnam(sys_get_temp_dir(), 'r') . '.xlsx';
    file_put_contents($local, Storage::disk(FailedRowsWriter::disk())->get($path));

    return ReaderFactory::make($local);
}

it('keeps a single row and errors column however often a report is fixed and uploaded again', function () {
    $path = FailedRowsWriter::write(['name', 'email'], [new RowFailure(2, ['name' => 'Ada', 'email' => 'bad'], ['round 1'])]);

    foreach (['round 2', 'round 3'] as $round) {
        $reader = reportReader($path);
        $headers = $reader->headers();
        $rows = iterator_to_array($reader->rows(), false);

        $path = FailedRowsWriter::write($headers, [new RowFailure(2, $rows[0], [$round])]);
    }

    $reader = reportReader($path);
    $row = iterator_to_array($reader->rows(), false)[0];

    expect($reader->headers())->toBe(['name', 'email', 'Row', 'Errors'])
        ->and($row['Errors'])->toBe('round 3');
});

it('recognizes a report downloaded in another language', function () {
    app()->setLocale('uk');
    $path = FailedRowsWriter::write(['name'], [new RowFailure(2, ['name' => 'Ada'], ['x'])]);
    $ukrainian = reportReader($path)->headers();

    app()->setLocale('en');

    expect(FailedRowsWriter::withoutPreviousReportColumns($ukrainian))->toBe(['name']);
});

it('keeps a lone Errors column that belongs to the user', function () {
    expect(FailedRowsWriter::withoutPreviousReportColumns(['name', 'Errors']))->toBe(['name', 'Errors']);
});
