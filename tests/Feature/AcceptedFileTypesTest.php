<?php

use Illuminate\Http\UploadedFile;
use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));
});

function csvLabelled(string $mimeType, string $name = 'customers.csv'): UploadedFile
{
    $file = UploadedFile::fake()->create($name, 1, $mimeType);
    file_put_contents($file->getPathname(), "name,email\nAda,ada@example.com\n");

    return $file;
}

it('uses the default accepted file types unless they are replaced', function () {
    expect(ExcelImportAction::make()->getAcceptedFileTypes())->toBe(ExcelImportAction::ACCEPTED_FILE_TYPES)
        ->and(ExcelImportAction::make()->acceptedFileTypes(['text/csv'])->getAcceptedFileTypes())->toBe(['text/csv'])
        ->and(ExcelImportAction::make()->acceptedFileTypes(fn () => ['text/csv'])->getAcceptedFileTypes())->toBe(['text/csv']);
});

it('refuses a CSV labelled with a type outside the defaults', function () {
    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => csvLabelled('application/octet-stream')])
        ->assertHasFormErrors(['file']);

    expect(Customer::count())->toBe(0);
});

it('imports a CSV labelled with an added type', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithExtraTypes')
        ->fillForm(['file' => csvLabelled('application/octet-stream')])
        ->callMountedAction()
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Customer::where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('still refuses an unreadable file when its type is accepted', function () {
    $file = UploadedFile::fake()->create('photo.bin', 1, 'application/octet-stream');
    file_put_contents($file->getPathname(), "\x89PNG\r\n\x1a\n\0\0\0\rIHDR");

    livewire(ListCustomers::class)
        ->mountAction('importWithExtraTypes')
        ->fillForm(['file' => $file])
        ->assertHasFormErrors(['file']);

    expect(Customer::count())->toBe(0);
});

it('reads a tab-separated .tsv file', function () {
    $file = UploadedFile::fake()->create('customers.tsv', 1, 'text/tab-separated-values');
    file_put_contents($file->getPathname(), "name\temail\nAda\tada@example.com\n");

    livewire(ListCustomers::class)
        ->mountAction('importWithExtraTypes')
        ->fillForm(['file' => $file])
        ->assertHasFormErrors(['file']); // text/tab-separated-values is not in this action's list

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => csvLabelled('text/plain', 'customers.tsv')])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::where('email', 'ada@example.com')->exists())->toBeTrue();
});
