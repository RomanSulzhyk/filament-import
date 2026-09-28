<?php

use Filament\Actions\Imports\ImportColumn;
use RomanSulzhyk\FilamentImport\Importing\TemplateWriter;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));
});

it('writes one header per column and the columns\' examples as text', function () {
    $path = TemplateWriter::write([
        ImportColumn::make('name')->example('Ada Lovelace'),
        ImportColumn::make('phone')->examples(['0044 20 7946 0000', '=1']),
        ImportColumn::make('city')->exampleHeader('Town'),
    ]);
    rename($path, $path .= '.xlsx');

    $reader = ReaderFactory::make($path);

    expect($reader->headers())->toBe(['name', 'phone', 'Town'])
        ->and(iterator_to_array($reader->rows(), false))->toBe([
            ['name' => 'Ada Lovelace', 'phone' => '0044 20 7946 0000', 'Town' => ''],
            ['name' => '', 'phone' => '=1', 'Town' => ''],
        ]);
});

it('downloads a template of the import columns from the modal', function () {
    livewire(ListCustomers::class)
        ->callAction(['excelImport', 'downloadTemplate'])
        ->assertFileDownloaded('customers-template.xlsx');
});

it('leaves fixed-value columns out of the template', function () {
    $component = livewire(ListCustomers::class)->instance();
    $action = $component->getAction('importWithFixedCity');

    expect(array_map(fn ($column) => $column->getName(), $action->getImportColumns()))->not->toContain('city');
});
