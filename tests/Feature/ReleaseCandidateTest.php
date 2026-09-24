<?php

/*
 * Regression tests for the independent review of the release candidate
 * (2026-09-24). Each test is named after the finding it pins down.
 */

use Filament\Actions\Imports\ImportColumn;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use RomanSulzhyk\FilamentImport\Reading\CsvReader;
use RomanSulzhyk\FilamentImport\Reading\HeaderRow;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Reading\UnsupportedSpreadsheet;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Files;

function rcMatch(array $headers, array $columns): array
{
    return (new HeuristicColumnMatcher)->match($headers, array_map(fn ($c) => ImportColumn::make($c), $columns));
}

function rcFile(string $content, string $suffix = '.csv'): string
{
    $path = tempnam(sys_get_temp_dir(), 'rc') . $suffix;
    file_put_contents($path, $content);

    return $path;
}

function rcSheet(string $rowsXml): string
{
    return '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
        . '<row r="1"><c r="A1" t="inlineStr"><is><t>name</t></is></c></row>' . $rowsXml . '</sheetData></worksheet>';
}

it('B1: reads Western Windows-1252 files with €, £ and one-letter accented words', function (string $csv, string $column, string $expected) {
    $path = rcFile(mb_convert_encoding($csv, 'Windows-1252', 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe('Windows-1252')
        ->and(iterator_to_array((new CsvReader($path))->rows(), false)[0][$column])->toBe($expected);
})->with([
    'euro prices' => ["sku;name;price\nA1;Coffee mug;€ 12,50\nA2;Tea pot;€ 30,00\n", 'price', '€ 12,50'],
    'pound prices' => ["sku,name,price\nA1,Mug,£12.50\nA2,Pot,£30.00\n", 'price', '£12.50'],
    'French' => ["nom,note\nDupont,livré à domicile\nMartin,payé à la livraison\nBernard,à rappeler\n", 'note', 'livré à domicile'],
    'Portuguese' => ["nome,obs\nJoão,cliente é vip\nMaria,é urgente\n", 'obs', 'cliente é vip'],
]);

it('B1: still reads short Ukrainian files as Windows-1251', function () {
    $path = rcFile(mb_convert_encoding("товар,ціна\nЧай,100 грн\n", 'Windows-1251', 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe('Windows-1251');
});

it('M1: counts every archive entry, including one whose name ends in a slash', function () {
    config()->set('filament-import.xlsx_max_uncompressed_bytes', 5 * 1024 * 1024);

    $path = Files::xlsx([['name'], ['Ada']]);
    $zip = new ZipArchive;
    $zip->open($path);
    $zip->addFromString('xl/worksheets/sheet1.xml', rcSheet(str_repeat('<row/>', 2_000_000)));
    $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $zip->addFromString('xl/_rels/workbook.xml.rels', str_replace('worksheets/sheet1.xml', 'worksheets/sheet1.xm/', $rels));
    $zip->close();
    file_put_contents($path, str_replace('xl/worksheets/sheet1.xml', 'xl/worksheets/sheet1.xm/', file_get_contents($path)));
    clearstatcache();

    expect(fn () => ReaderFactory::make($path))->toThrow(UnsupportedSpreadsheet::class);
});

it('A5: refuses a zip that is not a workbook before inflating it', function () {
    $path = tempnam(sys_get_temp_dir(), 'rc') . '.xlsx';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);
    $zip->addFromString('readme.txt', 'not a spreadsheet');
    $zip->close();

    expect(fn () => ReaderFactory::make($path))->toThrow(UnsupportedSpreadsheet::class);
});

it('M5: refuses a sheet whose row numbers run past Excel\'s limit', function () {
    $path = Files::xlsx([['name']]);
    $zip = new ZipArchive;
    $zip->open($path);
    $zip->addFromString('xl/worksheets/sheet1.xml', rcSheet('<row r="2000000"><c r="A2000000" t="inlineStr"><is><t>x</t></is></c></row>'));
    $zip->close();
    clearstatcache();

    $reader = ReaderFactory::make($path);

    expect(fn () => $reader->headers())->toThrow(UnsupportedSpreadsheet::class);
});

it('A4: ignores a stray value far beyond the headed columns', function () {
    $row = array_fill(0, 300, '');
    $row[0] = 'Ada';
    $row[299] = 'stray';

    expect(HeaderRow::clean(['name'], [$row]))->toBe(['name']);
});

it('M2: never swaps related fields, whatever the header order', function (array $headers, array $columns, array $expected) {
    expect(rcMatch($headers, $columns))->toBe($expected);
})->with([
    'phone and mobile' => [['Мобільний', 'Телефон'], ['phone', 'mobile'], ['phone' => 'Телефон', 'mobile' => 'Мобільний']],
    'phone and mobile, German' => [['Handy', 'Telefon'], ['phone', 'mobile'], ['phone' => 'Telefon', 'mobile' => 'Handy']],
    'address and street' => [['Вулиця', 'Адреса'], ['address', 'street'], ['address' => 'Адреса', 'street' => 'Вулиця']],
    'address and street, German' => [['Straße', 'Adresse'], ['address', 'street'], ['address' => 'Adresse', 'street' => 'Straße']],
    'notes and comment' => [['Коментар', 'Примітка'], ['notes', 'comment'], ['notes' => 'Примітка', 'comment' => 'Коментар']],
]);

it('M3: does not suggest words that usually mean another field', function () {
    expect(rcMatch(['Nome', 'Cidade', 'Estado'], ['name', 'city', 'state', 'status'])['status'])->toBeNull()
        ->and(rcMatch(['Nome', 'Città', 'Stato'], ['name', 'city', 'country', 'status'])['status'])->toBeNull()
        ->and(rcMatch(["Ім'я", 'Телефон', 'Місто', 'Пошта'], ['name', 'email', 'phone', 'city'])['email'])->toBeNull();
});

it('A8: a first-name header does not fill a full-name field when the file has a surname column', function () {
    expect(rcMatch(["Ім'я", 'Прізвище'], ['name', 'last_name']))->toBe(['name' => null, 'last_name' => 'Прізвище'])
        ->and(rcMatch(['Nome', 'Cognome'], ['name', 'surname']))->toBe(['name' => null, 'surname' => 'Cognome'])
        ->and(rcMatch(["Ім'я", 'Місто'], ['name', 'city']))->toBe(['name' => "Ім'я", 'city' => 'Місто']);
});

it('A8: a first-name header that fits both name and first_name is suggested for neither', function () {
    expect(rcMatch(["Ім'я"], ['name', 'first_name']))->toBe(['name' => null, 'first_name' => null]);
});

it('N1: reads Ukrainian Windows-1251 files typed with a Latin "i"', function () {
    $csv = "Iм'я,Прiзвище,Мiсто\nОлена,Коваленко,Київ\nIван,Петренко,Львiв\nМарiя,Шевчук,Харкiв\nПетро,Бондар,Днiпро\n";
    $path = rcFile(mb_convert_encoding($csv, 'Windows-1251', 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe('Windows-1251')
        ->and(iterator_to_array((new CsvReader($path))->rows(), false)[0]['Мiсто'])->toBe('Київ');
});

it('N1: still reads Western files with words that contain an "i"', function () {
    $path = rcFile(mb_convert_encoding("nome,città\nNiccolò,Città\nLouis,Île\n", 'Windows-1252', 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe('Windows-1252');
});

class RcTeamWithCustomers extends \RomanSulzhyk\FilamentImport\Tests\Fixtures\Team
{
    protected $table = 'teams';

    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::class, 'team_id');
    }
}

class RcCustomersRelationManager extends \Filament\Resources\RelationManagers\RelationManager
{
    protected static string $relationship = 'customers';
}

class RcSubTeamCustomer extends \RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer
{
    protected $table = 'team_customers';
}

function rcTenant(): \RomanSulzhyk\FilamentImport\Tests\Fixtures\Team
{
    $team = \RomanSulzhyk\FilamentImport\Tests\Fixtures\Team::create(['name' => 'Mine']);
    test()->actingAs(\RomanSulzhyk\FilamentImport\Tests\Fixtures\User::create(['name' => 'R', 'email' => 'r@example.com', 'password' => 'x']));
    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('tenant'));
    \Filament\Facades\Filament::setTenant($team);

    return $team;
}

function rcRelationManager(\RomanSulzhyk\FilamentImport\Tests\Fixtures\Team $team): RcCustomersRelationManager
{
    $manager = new RcCustomersRelationManager;
    $manager->ownerRecord = RcTeamWithCustomers::find($team->id);
    $manager->pageClass = \RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListTeamCustomers::class;

    return $manager;
}

function rcNames(\RomanSulzhyk\FilamentImport\Actions\ExcelImportAction $action): array
{
    return array_map(fn ($column) => $column->getName(), $action->getImportColumns());
}

it('N2: refuses zero-config mode in a relation manager of a tenant-scoped resource', function () {
    $action = \RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::make()
        ->importModel(\RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::class)
        ->upsertBy('email');
    $action->livewire(rcRelationManager(rcTenant()));

    expect(fn () => $action->getImportColumns())->toThrow(LogicException::class, 'in a relation manager');
});

it('N2: with explicit columns in a relation manager, the tenant key is still never offered', function () {
    $action = \RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::make()
        ->importModel(\RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::class)
        ->importColumns(['team_id', 'name', 'email']);
    $action->livewire(rcRelationManager(rcTenant()));

    expect(rcNames($action))->toBe(['name', 'email']);
});

it('refuses a subclass of the resource model, which Filament would not assign a tenant', function () {
    rcTenant();

    $action = \RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::make()->importModel(RcSubTeamCustomer::class);
    $action->livewire(new \RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListTeamCustomers);

    expect(fn () => $action->getImportColumns())->toThrow(LogicException::class);
});

it('still imports the resource model itself on a tenant panel, without offering the tenant key', function () {
    rcTenant();

    $action = \RomanSulzhyk\FilamentImport\Actions\ExcelImportAction::make();
    $action->livewire(new \RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListTeamCustomers);

    expect(rcNames($action))->toBe(['name', 'email']);
});

it('N3: refuses a corrupt archive entry without an error-log entry', function () {
    \Illuminate\Support\Facades\Exceptions::fake();

    $path = Files::xlsx([['name'], ['Ada']]);
    $bytes = file_get_contents($path);
    $zip = new ZipArchive;
    $zip->open($path);
    $stat = $zip->statName('xl/worksheets/sheet1.xml');
    $zip->close();

    // Corrupt the middle of the compressed sheet data.
    $offset = strpos($bytes, 'xl/worksheets/sheet1.xml') + strlen('xl/worksheets/sheet1.xml') + 10;
    $bytes = substr_replace($bytes, str_repeat("\xFF", min(40, $stat['comp_size'] - 20)), $offset, min(40, $stat['comp_size'] - 20));
    file_put_contents($path, $bytes);
    clearstatcache();

    expect(fn () => ReaderFactory::make($path))->toThrow(UnsupportedSpreadsheet::class);
    \Illuminate\Support\Facades\Exceptions::assertNothingReported();
});
