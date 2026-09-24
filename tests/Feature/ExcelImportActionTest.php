<?php

use RomanSulzhyk\FilamentImport\Importing\ImportResult;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Admin;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));
    ListCustomers::$afterImportCalls = [];
});

/**
 * The last notification sent, read the same way Filament's own
 * assertNotified() reads it: by mounting the notifications component.
 */
function lastNotification(): Notification
{
    $component = new \Filament\Notifications\Livewire\Notifications;
    $component->mount();

    return $component->notifications->last();
}

function upload(string $path, string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, file_get_contents($path));
}

it('shows on the resource list page', function () {
    livewire(ListCustomers::class)
        ->assertActionExists('excelImport')
        ->assertActionVisible('excelImport');
});

it('imports an xlsx with messy headers in zero-config mode, with no queue', function () {
    Queue::fake();

    $file = upload(Files::xlsx([
        ['Name', 'E-mail Address', 'Phone Number', 'City'],
        ['Ada Lovelace', 'ada@example.com', 5550101, 'London'],
        ['Alan Turing', 'alan@example.com', 5550102, 'Wilmslow'],
    ]), 'customers.xlsx');

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => $file])
        ->assertSchemaStateSet([
            'columnMap.name' => 'Name',
            'columnMap.email' => 'E-mail Address',
            'columnMap.phone' => 'Phone Number',
            'columnMap.city' => 'City',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Customer::count())->toBe(2)
        ->and(Customer::firstWhere('email', 'ada@example.com')->phone)->toBe('5550101')
        ->and(ListCustomers::$afterImportCalls)->toHaveCount(1)
        ->and(ListCustomers::$afterImportCalls[0])->toBeInstanceOf(ImportResult::class);

    Queue::assertNothingPushed();
});

it('lets the user correct a suggested mapping before importing', function () {
    $file = upload(Files::csv([
        ['Customer', 'Contact', 'Town'],
        ['Ada Lovelace', 'ada@example.com', 'London'],
    ]), 'customers.csv');

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => $file])
        ->fillForm(['columnMap' => ['name' => 'Customer', 'email' => 'Contact', 'phone' => null, 'city' => 'Town', 'joined_at' => null]])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::first()->only(['name', 'email', 'city']))->toBe([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'city' => 'London',
    ]);
});

it('runs an existing core importer without queues and offers the failed rows', function () {
    Queue::fake();

    $file = upload(Files::csv([
        ['name', 'e-mail', 'phone', 'city', 'signup date'],
        ['Ada Lovelace', 'ada@example.com', '', 'London', '2024-01-15'],
        ['Broken Row', 'not-an-email', '', '', ''],
    ]), 'customers.csv');

    livewire(ListCustomers::class)
        ->mountAction('importWithImporter')
        ->fillForm(['file' => $file])
        ->assertSchemaStateSet([
            'columnMap.email' => 'e-mail',
            'columnMap.joined_at' => 'signup date',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Customer::count())->toBe(1);
    Queue::assertNothingPushed();

    $notification = lastNotification();

    expect($notification->getBody())->toContain('1 created')->toContain('1 failed')
        ->and($notification->getActions()[0]->getUrl())->toContain('filament-import/failed-rows/');
});

it('serves the failed rows report only to the user who ran the import, as inert text cells', function () {
    livewire(ListCustomers::class)
        ->mountAction('importWithImporter')
        ->fillForm(['file' => upload(Files::csv([
            ['name', 'email', '=HYPERLINK("http://evil","header")'],
            ['=HYPERLINK("http://evil")', 'not-an-email', 'x'],
        ]), 'bad.csv')])
        ->callMountedAction();

    $url = lastNotification()->getActions()[0]->getUrl();

    $download = $this->get($url);
    $download->assertOk();

    $report = tempnam(sys_get_temp_dir(), 'report') . '.xlsx';
    file_put_contents($report, $download->streamedContent());

    // No cell, header row included, is stored as a formula.
    $zip = new ZipArchive;
    $zip->open($report);
    expect($zip->getFromName('xl/worksheets/sheet1.xml'))->not->toContain('<f>');
    $zip->close();

    $rows = iterator_to_array(\RomanSulzhyk\FilamentImport\Reading\ReaderFactory::make($report)->rows(), false);
    expect($rows[0]['name'])->toBe('=HYPERLINK("http://evil")')
        ->and($rows[0]['email'])->toBe('not-an-email')
        ->and(array_keys($rows[0]))->toContain('=HYPERLINK("http://evil","header")');

    $this->actingAs(User::create(['name' => 'Other', 'email' => 'other@example.com', 'password' => 'x']));
    $this->get($url)->assertForbidden();

    $this->get(preg_replace('/signature=[^&]+/', 'signature=forged', $url))->assertForbidden();

    auth()->logout();
    $this->get($url)->assertForbidden();
});

it('refuses a file larger than the immediate-import limit, without writing', function () {
    config(['filament-import.sync_row_limit' => 2]);

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => upload(Files::csv([
            ['name', 'email'],
            ['A', 'a@example.com'], ['B', 'b@example.com'], ['C', 'c@example.com'],
        ]), 'big.csv')])
        ->callMountedAction();

    expect(Customer::count())->toBe(0)
        ->and(lastNotification()->getBody())->toContain('more than 2 rows');
});

it('M1: on a panel with its own guard, only the admin who ran the import can download the report', function () {
    Filament::getCurrentOrDefaultPanel()->authGuard('admin');

    $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'x']);
    auth()->guard('web')->logout();
    $this->actingAs($admin, 'admin');

    livewire(ListCustomers::class)
        ->mountAction('importWithImporter')
        ->fillForm(['file' => upload(Files::csv([['name', 'email'], ['Bad', 'not-an-email']]), 'bad.csv')])
        ->callMountedAction();

    $url = lastNotification()->getActions()[0]->getUrl();

    expect($url)->toContain('guard=admin');

    $this->get($url)->assertOk();

    // A different person on the default guard who happens to have the same id.
    auth()->guard('admin')->logout();
    $sameId = User::query()->find($admin->getKey()) ?? User::create(['name' => 'Same Id', 'email' => 'same@example.com', 'password' => 'x']);
    expect((string) $sameId->getKey())->toBe((string) $admin->getKey());
    $this->actingAs($sameId, 'web');

    $this->get($url)->assertForbidden();
});

it('M4: the form refuses to import while a required field is left unmapped', function () {
    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => upload(Files::csv([['Customer', 'Contact'], ['Ada', 'ada@example.com']]), 'c.csv')])
        ->fillForm(['columnMap' => ['name' => null, 'email' => 'Contact', 'phone' => null, 'city' => null, 'joined_at' => null]])
        ->callMountedAction()
        ->assertHasFormErrors(['columnMap.name' => 'required']);

    expect(Customer::count())->toBe(0);
});

it('A4: refuses zero-config options combined with an importer class instead of ignoring them', function () {
    $action = \RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::make()
        ->importer(CustomerImporter::class)
        ->upsertBy('email');

    expect(fn () => $action->getImportColumns())->toThrow(LogicException::class, 'upsertBy()');
});

it('A8: shows why an .xls upload was refused on the file field', function () {
    $xls = tempnam(sys_get_temp_dir(), 'legacy');
    file_put_contents($xls, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\0", 512));

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('old.xls', file_get_contents($xls))])
        ->assertHasFormErrors(['file']);
});


it('refuses an old .xls with the save-as-xlsx message, whatever type it is detected as', function () {
    $component = livewire(ListCustomers::class)->mountAction('excelImport');

    // A real .xls is detected as vnd.ms-excel or as generic OLE storage,
    // depending on the system. Both must reach the reader, which explains
    // what to do instead of showing a generic file-type error.
    expect(\RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::ACCEPTED_FILE_TYPES)->toContain('application/vnd.ms-excel', 'application/x-ole-storage', 'application/CDFV2', 'application/zip');

    $component->fillForm(['file' => UploadedFile::fake()->createWithContent('old.xls', "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\0", 504))]);

    expect(collect($component->errors()->all())->implode(' '))->toContain('save it as .xlsx');
});

it('auto-matches a Windows-1251 CSV with Ukrainian headers', function () {
    $content = mb_convert_encoding("Ім'я,Email,Місто\nТарас Шевченко,taras@example.com,Київ\n", 'Windows-1251', 'UTF-8');

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('ua.csv', $content)])
        ->assertSchemaStateSet([
            'columnMap.name' => "Ім'я",
            'columnMap.email' => 'Email',
            'columnMap.city' => 'Місто',
        ])
        ->callMountedAction();

    expect(Customer::where('email', 'taras@example.com')->value('city'))->toBe('Київ');
});
