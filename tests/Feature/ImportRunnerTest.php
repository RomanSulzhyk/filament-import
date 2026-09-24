<?php

use RomanSulzhyk\FilamentImport\Importing\FillableImporter;
use RomanSulzhyk\FilamentImport\Importing\ImportRunner;
use RomanSulzhyk\FilamentImport\Importing\TooManyRowsForSyncImport;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use Illuminate\Support\Facades\Queue;

function runImport(string $path, array $columnMap, string $importer = CustomerImporter::class, ?int $limit = null)
{
    return (new ImportRunner(ReaderFactory::make($path), $importer, $columnMap, syncRowLimit: $limit))->run();
}

$identity = ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'city' => 'city', 'joined_at' => 'joined_at'];

it('runs an unmodified core Filament importer synchronously, with no queue', function () use ($identity) {
    Queue::fake();

    $path = Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', '555-0101', 'London', '2024-01-15'],
        ['Alan Turing', 'alan@example.com', '555-0102', 'Wilmslow', '2024-02-20'],
        ['Grace Hopper', 'grace@example.com', '', 'Arlington', ''],
    ]);

    $result = runImport($path, $identity);

    expect($result->created)->toBe(3)
        ->and($result->failed())->toBe(0)
        ->and(Customer::count())->toBe(3)
        ->and(Customer::firstWhere('email', 'ada@example.com')->joined_at->toDateString())->toBe('2024-01-15');

    Queue::assertNothingPushed();
});

it('records a failed row with its spreadsheet line number and keeps importing the rest', function () use ($identity) {
    $path = Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', '', '', ''],
        ['Broken Row', 'not-an-email', '', '', ''],
        ['Alan Turing', 'alan@example.com', '', '', ''],
    ]);

    $result = runImport($path, $identity);

    expect($result->created)->toBe(2)
        ->and($result->failed())->toBe(1)
        ->and($result->failures[0]->row)->toBe(3)
        ->and($result->failures[0]->data['email'])->toBe('not-an-email')
        ->and(implode(' ', $result->failures[0]->messages))->toContain('email')
        ->and(Customer::count())->toBe(2);
});

it('updates existing records when the importer resolves them', function () use ($identity) {
    Customer::create(['name' => 'Old Name', 'email' => 'ada@example.com']);

    $result = runImport(Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', '555-0101', 'London', ''],
        ['Alan Turing', 'alan@example.com', '', '', ''],
    ]), $identity);

    expect($result->updated)->toBe(1)
        ->and($result->created)->toBe(1)
        ->and(Customer::firstWhere('email', 'ada@example.com')->name)->toBe('Ada Lovelace');
});

it('reads semicolon-delimited CSV with a byte order mark', function () use ($identity) {
    $result = runImport(Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', '', 'London', ''],
    ], delimiter: ';', bom: true), $identity);

    expect($result->created)->toBe(1)
        ->and(Customer::first()->name)->toBe('Ada Lovelace')
        ->and(Customer::first()->city)->toBe('London');
});

it('decodes Windows-1251 files exported from Excel', function () use ($identity) {
    $result = runImport(Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Тарас Шевченко', 'taras@example.com', '', 'Київ', ''],
    ], delimiter: ';', encoding: 'Windows-1251'), $identity);

    expect($result->created)->toBe(1)
        ->and(Customer::first()->name)->toBe('Тарас Шевченко')
        ->and(Customer::first()->city)->toBe('Київ');
});

it('reads xlsx files, including real Excel dates and numeric cells', function () use ($identity) {
    $result = runImport(Files::xlsx([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', 5550101, 'London', new DateTimeImmutable('2024-03-05')],
    ]), $identity);

    $ada = Customer::first();

    expect($result->created)->toBe(1)
        ->and($ada->joined_at->toDateString())->toBe('2024-03-05')
        ->and($ada->phone)->toBe('5550101');
});

it('skips fully blank rows and still reports the real line numbers', function () use ($identity) {
    $result = runImport(Files::csv([
        ['name', 'email', 'phone', 'city', 'joined_at'],
        ['Ada Lovelace', 'ada@example.com', '', '', ''],
        ['', '', '', '', ''],
        ['Broken', 'nope', '', '', ''],
    ]), $identity);

    expect($result->created)->toBe(1)
        ->and($result->failures[0]->row)->toBe(4);
});

it('refuses an oversized file before writing a single row', function () use ($identity) {
    $rows = [['name', 'email', 'phone', 'city', 'joined_at']];

    foreach (range(1, 6) as $i) {
        $rows[] = ["Person {$i}", "p{$i}@example.com", '', '', ''];
    }

    expect(fn () => runImport(Files::csv($rows), $identity, limit: 5))->toThrow(TooManyRowsForSyncImport::class)
        ->and(Customer::count())->toBe(0);
});

it('imports into any model with no importer class, from messy headers', function () {
    $path = Files::csv([
        ['Full Name', 'E-mail Address', 'Phone Number', 'City'],
        ['Ada Lovelace', 'ada@example.com', '555-0101', 'London'],
    ]);

    $reader = ReaderFactory::make($path);
    $columns = FillableImporter::columnsFor(Customer::class, ['email' => 'required|email']);
    $map = (new HeuristicColumnMatcher)->match($reader->headers(), $columns);

    expect($map)->toMatchArray([
        'email' => 'E-mail Address',
        'phone' => 'Phone Number',
        'city' => 'City',
    ]);

    $map['name'] = 'Full Name';

    $result = (new ImportRunner(
        $reader,
        fn ($import, $columnMap, $options) => (new FillableImporter($import, $columnMap, $options))->using(Customer::class, $columns),
        $map,
    ))->run();

    expect($result->created)->toBe(1)
        ->and(Customer::first()->only(['name', 'email', 'phone', 'city']))->toBe([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '555-0101',
            'city' => 'London',
        ]);
});

it('upserts by key in zero-config mode', function () {
    Customer::create(['name' => 'Old Name', 'email' => 'ada@example.com', 'city' => 'Paris']);

    $reader = ReaderFactory::make(Files::csv([
        ['name', 'email', 'city'],
        ['Ada Lovelace', 'ada@example.com', 'London'],
        ['Alan Turing', 'alan@example.com', 'Wilmslow'],
    ]));
    $columns = FillableImporter::columnsFor(Customer::class);

    $result = (new ImportRunner(
        $reader,
        fn ($import, $map, $options) => (new FillableImporter($import, $map, $options))->using(Customer::class, $columns, ['email']),
        ['name' => 'name', 'email' => 'email', 'city' => 'city'],
    ))->run();

    expect($result->updated)->toBe(1)
        ->and($result->created)->toBe(1)
        ->and(Customer::count())->toBe(2)
        ->and(Customer::firstWhere('email', 'ada@example.com')->only(['name', 'city']))->toBe(['name' => 'Ada Lovelace', 'city' => 'London']);
});

it('never lets a blank upsert key overwrite an existing record', function () {
    Customer::create(['name' => 'Keep Me', 'email' => 'keep@example.com', 'phone' => '']);

    $reader = ReaderFactory::make(Files::csv([
        ['name', 'email', 'phone'],
        ['Intruder', 'intruder@example.com', ''],
    ]));
    $columns = FillableImporter::columnsFor(Customer::class);

    (new ImportRunner(
        $reader,
        fn ($import, $map, $options) => (new FillableImporter($import, $map, $options))->using(Customer::class, $columns, ['phone']),
        ['name' => 'name', 'email' => 'email', 'phone' => 'phone'],
    ))->run();

    expect(Customer::firstWhere('email', 'keep@example.com')->name)->toBe('Keep Me')
        ->and(Customer::count())->toBe(2);
});
