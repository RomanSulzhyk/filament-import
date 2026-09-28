<?php

use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerOptionsImporter;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));
});

function optionsUpload(array $rows): UploadedFile
{
    return UploadedFile::fake()->createWithContent('customers.csv', file_get_contents(Files::csv($rows)));
}

it('writes fixed values to every record and never offers them for mapping', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithFixedCity')
        ->fillForm(['file' => optionsUpload([['name', 'email', 'city'], ['Ada', 'ada@example.com', 'London']])])
        ->assertSchemaStateSet(['columnMap.name' => 'name', 'columnMap.email' => 'email'])
        ->assertFormFieldDoesNotExist('columnMap.city')
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::firstWhere('email', 'ada@example.com')->city)->toBe('Kyiv');
});

it('fills fixed values from a field in the modal, over any file column', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithDefaults')
        ->fillForm([
            'file' => optionsUpload([['name', 'email', 'city'], ['Ada', 'ada@example.com', 'London']]),
            'default_city' => 'Lviv',
        ])
        ->set('mountedActions.0.data.columnMap.city', 'city')
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::firstWhere('email', 'ada@example.com')->city)->toBe('Lviv');
});

it('shows an importer\'s own options fields and passes their values as options', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithOptions')
        ->fillForm([
            'file' => optionsUpload([['name', 'email'], ['Ada', 'ada@example.com']]),
            'city' => 'Odesa',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::firstWhere('email', 'ada@example.com')->city)->toBe('Odesa')
        ->and(CustomerOptionsImporter::$seenOptions)->toMatchArray(['source' => 'code', 'city' => 'Odesa']);
});

it('requires the importer\'s options fields like any other field', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithOptions')
        ->fillForm(['file' => optionsUpload([['name', 'email'], ['Ada', 'ada@example.com']])])
        ->callMountedAction()
        ->assertHasFormErrors(['city' => 'required']);

    expect(Customer::count())->toBe(0);
});

it('uses a fixed value to find the record an upsert updates', function () {
    Customer::create(['name' => 'Old', 'email' => 'ada@example.com', 'city' => 'Kyiv']);

    $importer = (new \RomanSulzhyk\FilamentImport\Importing\FillableImporter(new \Filament\Actions\Imports\Models\Import, ['name' => 'name', 'email' => 'email'], []))
        ->using(Customer::class, \RomanSulzhyk\FilamentImport\Importing\FillableImporter::columnsFor(Customer::class, [], ['email', 'city'], null, ['city']), ['email', 'city'], ['city' => 'Kyiv']);

    $importer(['name' => 'New', 'email' => 'ada@example.com']);

    expect(Customer::count())->toBe(1)
        ->and(Customer::first()->name)->toBe('New');
});
