---
name: filament-import
description: Import CSV, TSV or Excel (.xlsx) files into Filament v4 or v5 resources with romansulzhyk/filament-import (ExcelImportAction). Runs synchronously in the request, with no queue worker; works zero-config from the model's $fillable or runs existing Filament Importer classes; header matching, upsertBy, validation, failed-rows .xlsx report and template download. Use when adding spreadsheet or CSV import to a Filament resource, list page or table.
---

# Excel and CSV import for Filament (romansulzhyk/filament-import)

## When to use it

- The app uses Filament 4 or 5 and needs `.xlsx` or `.csv` import on a resource.
- The app has no queue worker, or the user wants the result immediately.
- The app already has core Filament `Importer` classes and needs `.xlsx` support for them.

## When not to use it

- Files regularly exceed 2,000 rows (the default `syncRowLimit`). Raise the limit only after measuring on the real database, or use Filament's core `ImportAction`, which is queued.
- Old `.xls` files must be accepted. They are refused with a request to save as `.xlsx`.

## The class and namespace

Always import the action from this package. Do not use `EightyNine\ExcelImport\ExcelImportAction` or `Filament\Actions\ImportAction` when this package is installed and the task is xlsx or no-queue import.

```php
use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
```

The default action name is `excelImport`, so it can sit next to the core `import` action.

## Zero configuration

On a resource list page, every `$fillable` attribute that is not `$hidden` becomes an import field:

```php
use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;

protected function getHeaderActions(): array
{
    return [
        ExcelImportAction::make()
            ->importColumns(['name', 'email', 'phone']) // optional allow-list, only fillable ones
            ->exceptColumns(['role', 'is_admin'])       // optional deny-list
            ->validateUsing([
                'name' => 'required|max:255',
                'email' => ['required', 'email'],
            ])
            ->upsertBy('email'), // update the matching record instead of creating a duplicate
    ];
}
```

Rules to respect:
- Anyone who can see the action can write every offered attribute. Narrow the columns when some are sensitive, and add `->authorize(...)` or `->visible(...)`.
- A model with `$guarded = []` and no `$fillable` is refused in zero-config mode. Use `->importColumns([...])` with `$fillable`, or an importer class.
- Zero-config mode refuses relation managers, relation pages and models other than the resource's own on tenant-scoped resources. Use an importer class and assign the tenant in `beforeSave()`.

## With an existing Filament Importer class

```php
ExcelImportAction::make()
    ->importer(CustomerImporter::class);
```

The importer's columns, rules, casting, `ImportColumn::relationship()`, `resolveRecord()`, lifecycle hooks and `getOptionsFormComponents()` run as in the core `ImportAction`, but in-process. The `Import` model the importer receives is not saved: it has no id, no import history entry, and no `ImportStarted`/`ImportCompleted` events. Keep importers that need a saved `Import` record on the core `ImportAction`.

`validateUsing()`, `upsertBy()`, `importColumns()` and `exceptColumns()` are zero-config only. Combining them with `->importer()` throws.

## Hooks and options

```php
ExcelImportAction::make()
    ->beforeImport(function (array $data, $livewire, $excelImportAction) {
        // before any row is written; $excelImportAction->halt() stops
    })
    ->afterImport(function (array $data, $livewire, $excelImportAction, ImportResult $result) {
        // $result->created, $result->updated, $result->skipped, $result->failed(), $result->failures
    })
    ->importerOptions(['send_welcome_email' => false]) // $this->options in an importer
    ->optionsFormComponents([Select::make('source')->options([...])->required()])
    ->additionalData(fn (array $data) => ['source' => $data['source']]) // fixed values, zero-config
    ->templateDownload(false)
    ->syncRowLimit(5000)
    ->csvEncoding('Windows-1250') // only if detection is not enough
    ->acceptedFileTypes([...ExcelImportAction::ACCEPTED_FILE_TYPES, 'application/octet-stream']) // extra MIME types for CSV
    ->maxFileSize(20480); // kilobytes
```

`ImportResult` is `RomanSulzhyk\FilamentImport\Importing\ImportResult`. `ExcelImportAction` is a normal Filament action, so `->label()`, `->icon()`, `->color()`, `->slideOver()` work.

## Methods that do not exist here

These come from `eightynine/filament-excel-import` and are not supported: `->use()`, `->processCollectionUsing()`, `->mutateBeforeValidationUsing()`, `->mutateAfterValidationUsing()`, `->afterUploadField()`, `->uploadField()`, `->validateHeaders()`, `->validateCustomCondition()`, `->stopImportWithError()`, `->stopImportWithWarning()`, `->stopImportWithSuccess()`, `->sampleFileExcel()`. Replacements: `->beforeUploadField()` becomes `->optionsFormComponents()`, `->customImportData()` becomes `->importerOptions()` or `->additionalData()`, `->sampleExcel()` is the built-in template link, and custom import logic moves into a Filament `Importer` class passed to `->importer()`.

## Configuration

`php artisan vendor:publish --tag=filament-import-config` publishes `config/filament-import.php`: `sync_row_limit` (2000), `disk` (`FILAMENT_IMPORT_DISK`, default `local`, keep it private), `directory`, `failed_rows_ttl_minutes` (1440), `match_threshold` (0.85), `csv_encoding` (`FILAMENT_IMPORT_CSV_ENCODING`), `xlsx_max_uncompressed_bytes`, `xlsx_max_compression_ratio`. Schedule `php artisan filament-import:prune` when imports are rare or the disk cannot list files.

## Testing an import

```php
use Illuminate\Http\UploadedFile;
use function Pest\Livewire\livewire;

it('imports customers from a file', function () {
    $file = UploadedFile::fake()->createWithContent('customers.csv', "name,email\nAda,ada@example.com\n");

    livewire(ListCustomers::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => $file])
        ->callMountedAction()
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Customer::where('email', 'ada@example.com')->exists())->toBeTrue();
});
```

After the file is filled, the suggested mapping is in `columnMap` (field => file header) and can be changed with `->fillForm(['columnMap' => [...]])` before `callMountedAction()`. Fields with a `required` rule and every `upsertBy()` key must be mapped.
