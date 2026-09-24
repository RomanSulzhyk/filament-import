# Excel & CSV Import for Filament

<div class="filament-hidden">

![Excel & CSV Import for Filament](https://raw.githubusercontent.com/RomanSulzhyk/filament-import/main/art/cover.jpg)

[![Tests](https://github.com/RomanSulzhyk/filament-import/actions/workflows/tests.yml/badge.svg)](https://github.com/RomanSulzhyk/filament-import/actions/workflows/tests.yml)
[![Latest version](https://img.shields.io/packagist/v/romansulzhyk/filament-import.svg)](https://packagist.org/packages/romansulzhyk/filament-import)
[![License](https://img.shields.io/packagist/l/romansulzhyk/filament-import.svg)](LICENSE.md)

</div>

Import `.xlsx` and `.csv` files into any Filament v4 or v5 resource, **immediately, with no queue worker**.

- Works with zero configuration, reading columns from your model's `$fillable`.
- Or runs **your existing Filament `Importer` classes**, without queues and with `.xlsx` support that the core importer does not have.
- Suggests which file column feeds which field, and lets the user correct it before importing.
- Reports every failed row with its spreadsheet line number, and offers the rejected rows as an `.xlsx` the user can fix and upload again.
- Reads what Excel really exports: Windows-1251 and Windows-1252 CSVs, semicolon delimiters, Excel dates, and headers in English, Ukrainian, Russian, German, French, Spanish, Italian, Portuguese, Polish and Dutch.

![Matching file columns to fields](https://raw.githubusercontent.com/RomanSulzhyk/filament-import/main/art/mapping.jpg)

## Requirements

| | Supported |
|---|---|
| PHP | 8.2+ |
| Laravel | 11.28+, 12, 13 |
| Filament | 4.0+, 5.x |

## Installation

```bash
composer require romansulzhyk/filament-import
```

The package registers itself. To change the defaults, publish the config:

```bash
php artisan vendor:publish --tag=filament-import-config
```

## Usage

### Zero configuration

Add the action to a resource's list page:

```php
use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;

class ListCustomers extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make(),
        ];
    }
}
```

Every `$fillable` attribute that is not `$hidden` becomes an import field. **Anyone who can see the action can write any of those attributes**, so narrow the list if some of them are sensitive:

```php
ExcelImportAction::make()
    ->importColumns(['name', 'email', 'phone'])   // only these, and only if fillable
    ->exceptColumns(['role', 'is_admin']);         // never these
```

On a tenant-scoped resource in a panel with tenancy, the tenant ownership key (for example `team_id`) is never offered. Filament only re-assigns the tenant when a record is created, so an imported value could otherwise move existing records to another tenant through `upsertBy()`.

Zero-config mode only imports the resource's own model, on the resource's own pages, when the resource is tenant-scoped. Filament assigns the tenant only in that case, so rows imported anywhere else (a relation manager, a relation page, another model or a subclass) would get no tenant, or a tenant or parent key taken from the file. The action refuses those placements with an explanation when its modal opens. To import there, list the columns with `->importColumns([...])`, which still never includes the tenant key, and assign the tenant yourself. The simplest way is an importer class:

```php
class ContactImporter extends Importer
{
    // ...columns...

    protected function beforeSave(): void
    {
        $this->record->team()->associate(Filament::getTenant());
    }
}

ExcelImportAction::make()->importer(ContactImporter::class);
```

A model that uses `$guarded = []` has no `$fillable`, so zero-config mode refuses it with an error. Use `->importColumns([...])` together with `$fillable`, or an importer class.

File headers are matched to fields automatically, and the user can correct any match before importing. Only strong matches are pre-selected:
- exact matches, such as `email` or `Email`;
- the same letters written differently: `E-mail`, `FirstName`, `first_name`;
- matches after dropping owner words or noise words: `Customer Email`, `E-mail Address`, `Phone Number`;
- near-identical spellings: `Adress`;
- whole headers that are a common name for the field in another language: `Місто`, `Telefon`, `Ciudad`, `Courriel`. A word that could fit two of your fields, such as a first name when the file also has a surname column, is left for the user to map.

![Ukrainian headers matched automatically](https://raw.githubusercontent.com/RomanSulzhyk/filament-import/main/art/multilingual.jpg)

A header that merely contains a field name is left for the user to map. Examples are `Username`, `Last Name`, `Company Name`, `Email Opt-in` and `Billing City`, because pre-selecting them would silently put data in the wrong column.

Add validation, and update existing records instead of duplicating them:

```php
ExcelImportAction::make()
    ->validateUsing([
        'name' => 'required|max:255',
        'email' => ['required', 'email'],
    ])
    ->upsertBy('email');
```

![Imported customers](https://raw.githubusercontent.com/RomanSulzhyk/filament-import/main/art/result.jpg)

`upsertBy()` updates the record whose values in those columns match the row, and creates one otherwise. A row with a blank key is always created, never matched, so an empty cell cannot overwrite an unrelated record.

Fields with a `required` rule, and every `upsertBy()` key, must be mapped before the import can start. Leaving them on "Do not import" would otherwise skip their validation or turn every update into a duplicate.

### With an existing Filament importer

If you already have a core `Importer` class, pass it in:

```php
ExcelImportAction::make()
    ->importer(CustomerImporter::class);
```

It runs the importer's own columns, validation rules, casting, relationships, `resolveRecord()` and lifecycle hooks, exactly as the core `ImportAction` would, but in-process instead of through a job batch. Relationships declared with `ImportColumn::relationship()`, upserts written in `resolveRecord()` and `guess()` hints all work unchanged.

`validateUsing()`, `upsertBy()`, `importColumns()` and `exceptColumns()` configure zero-config mode only. Combining them with `->importer()` throws an exception rather than silently ignoring them.

### Hooks and options

```php
ExcelImportAction::make()
    ->beforeImport(function (array $data, $livewire, $excelImportAction) {
        // Runs before any row is written. Call $excelImportAction->halt() to stop.
    })
    ->afterImport(function (array $data, $livewire, $excelImportAction, ImportResult $result) {
        // $result->created, ->updated, ->skipped, ->failed(), ->failures
    })
    ->importerOptions(['send_welcome_email' => false]) // Available to the importer as $this->options
    ->syncRowLimit(5000)
    ->maxFileSize(20480); // kilobytes
```

`ExcelImportAction` is a normal Filament action, so `->label()`, `->icon()`, `->color()`, `->slideOver()`, `->visible()`, `->authorize()` and every other action method work as usual.

## Limits and how it works

Each row goes through the importer's own per-row pipeline, in its own database transaction on the model's connection. One bad row does not stop the file. Because every row is its own transaction (a savepoint when nested), a failed statement cannot poison the rows after it. That matters on PostgreSQL, although the suite itself runs on SQLite only.

Only the **first worksheet** of an `.xlsx`, in workbook order, is read. Blank rows above the header are skipped, and line numbers in errors still match the file. A column that has data in the first 200 rows but no header is kept as `column_N`, so its values reach the failed-rows report. Such unheaded data columns are only added up to the 256th column; columns that do have a header are always kept. Sheets that claim more rows than Excel's limit of 1,048,576 are refused. Old `.xls` files are refused with a request to save them as `.xlsx`.

A CSV may be UTF-8 (with or without a BOM), Windows-1251 or Windows-1252, delimited by commas, semicolons, pipes or tabs. The encoding and delimiter are detected from the file.

A formula cell is imported as the result the spreadsheet app last saved for it. Files saved by Excel, LibreOffice or Google Sheets always carry that result. Two cases are not handled well:
- A formula whose result is an error, such as `#N/A` from a lookup, imports as the formula text with OpenSpout 4.32 and as an empty value with OpenSpout 4.23. Validate such columns if they can contain errors.
- A file generated by a library and never opened in a spreadsheet app may carry no saved result. A numeric formula then imports as `0`.

Before an `.xlsx` is parsed, the archive must contain a workbook, and every entry in it is decompressed and its real size counted, stopping as soon as a limit is passed. A file is refused when its contents would expand beyond 100 MB, or beyond 200 times the uploaded file's size (see `xlsx_max_uncompressed_bytes` and `xlsx_max_compression_ratio`). The sizes the archive declares about itself are not trusted.

Files larger than `sync_row_limit` rows (default **2,000**) are refused before anything is written, with a message telling the user to split the file. There is no queued mode in this version.

Measured on SQLite with an importer that validates five columns, on one machine:

| Rows | CSV | XLSX | Extra memory |
|---:|---:|---:|---:|
| 2,000 | 0.6 s | 0.9 s | ~2 MB |
| 10,000 | 3.1 s | 4.1 s | flat, the file is streamed |

A networked MySQL or PostgreSQL database is slower. Measure on your own database before raising the limit, and keep the result well inside your request timeout.

**Importers that need a saved `Import` record.** In this action the `Import` passed to your importer is an unsaved in-memory instance. `$this->getImport()->user` works. A hook that writes rows referencing `import_id`, or otherwise depends on the record existing in the database, does not. Keep those importers on the core `ImportAction`.

## Security

- The action authorizes like any Filament action. Add `->authorize(...)` or `->visible(...)` as you would elsewhere. As with the core importer, rows are not checked against model policies; put per-record checks in your importer's hooks.
- The package does not store uploads. The file stays in Livewire's temporary upload storage until Livewire's own cleanup removes it; on S3, that needs a bucket lifecycle rule. When the temporary upload is remote, a local copy is made for reading and deleted when the request terminates, including under Octane.
- The size limit (`maxFileSize()`) and the accepted file types are enforced before the file is parsed.
- The failed-rows report is an `.xlsx` written to the configured disk (`local` by default; keep it private). It contains every column of the rejected rows, including columns that were not imported, so the user can fix them and upload the file again.
- Every cell in the report is written as text. A value such as `=HYPERLINK(...)` is shown as text, never run as a formula, and it is read back unchanged on re-upload, as are values like `+15550101`.
![The failed-rows report link](https://raw.githubusercontent.com/RomanSulzhyk/filament-import/main/art/failed-rows.jpg)

- The report's download link is signed. It carries the panel's auth guard and the user's id, and works only for the user who ran the import, on that guard. No report is written when no user is signed in.
- Reports are deleted after `failed_rows_ttl_minutes` (default 24 hours, the same as the link). Old reports are pruned whenever a new one is written. A failure while pruning is reported to your exception handler and never fails the import. If imports are rare, or your disk does not allow listing files, schedule `php artisan filament-import:prune` as well.

## Coming from eighty9nine/filament-excel-import?

That plugin has no Filament v5 release. This package reproduces its default behaviour, matching file headers to `$fillable` attributes, and these of its methods:

| eighty9nine method | Here |
|---|---|
| `ExcelImportAction::make()` | Supported. Change the `use` import to `RomanSulzhyk\FilamentImport\Actions\ExcelImportAction`. |
| `->validateUsing([...])` | Supported, same signature. |
| `->beforeImport(fn ($data, $livewire, $excelImportAction) => ...)` | Supported, same arguments. |
| `->afterImport(fn ($data, $livewire, $excelImportAction) => ...)` | Supported. Also receives `$result`. |
| `->color()`, `->slideOver()`, `->label()`, `->icon()`, `->requiresConfirmation()` | Supported: standard Filament action methods. |

**Not supported in this version:**
- `->use(YourImport::class)`, because it requires `maatwebsite/excel`: move the logic into a Filament `Importer` class and pass it with `->importer()`.
- `->processCollectionUsing()`
- `->mutateBeforeValidationUsing()`
- `->mutateAfterValidationUsing()`
- `->beforeUploadField()`
- `->afterUploadField()`
- `->uploadField()`
- `->additionalData()`
- `->customImportData()`
- `->validateHeaders()`
- `->validateCustomCondition()`
- `->stopImportWithError()`
- `->stopImportWithWarning()`
- `->stopImportWithSuccess()`
- `->sampleExcel()`
- `->sampleFileExcel()`

The default action name is `excelImport`, so it can sit next to the core `import` action on the same page.

## Testing

```bash
composer install
vendor/bin/pest                                     # 98 tests
vendor/bin/pest tests/Performance --group=performance
```

CI runs the suite on PHP 8.2 to 8.5 against Filament 4 and 5 and Laravel 11, 12 and 13, at both the lowest and the newest allowed versions.

## Translations

English and Ukrainian are included. Publish the files to add or change a language:

```bash
php artisan vendor:publish --tag=filament-import-translations
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Security

Report security problems privately, as described in the [security policy](.github/SECURITY.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
