<?php

use RomanSulzhyk\FilamentImport\Importing\ImportRunner;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use RomanSulzhyk\FilamentImport\Tests\TestCase;

uses(TestCase::class);

/*
 * Not part of the default suite. Run with:
 *   vendor/bin/pest tests/Performance --group=performance
 */
it('measures sync throughput and memory', function (int $rows, string $format) {
    $data = [['name', 'email', 'phone', 'city', 'joined_at']];

    foreach (range(1, $rows) as $i) {
        $data[] = ["Person {$i}", "p{$i}@example.com", '555-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'City ' . ($i % 50), '2024-01-15'];
    }

    $path = $format === 'xlsx' ? Files::xlsx($data) : Files::csv($data);
    $map = ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'city' => 'city', 'joined_at' => 'joined_at'];

    gc_collect_cycles();
    $memoryBefore = memory_get_usage(true);
    $start = hrtime(true);

    $result = (new ImportRunner(ReaderFactory::make($path), CustomerImporter::class, $map, syncRowLimit: $rows))->run();

    $seconds = (hrtime(true) - $start) / 1e9;
    $peakMb = (memory_get_peak_usage(true) - $memoryBefore) / 1048576;

    fwrite(STDERR, sprintf("\n  %6d rows %-4s  %6.2fs  %6.0f rows/s  +%.1f MB peak\n", $rows, $format, $seconds, $rows / $seconds, $peakMb));

    expect($result->created)->toBe($rows);
})->with([
    [2000, 'csv'],
    [2000, 'xlsx'],
    [10000, 'csv'],
    [10000, 'xlsx'],
])->group('performance');
