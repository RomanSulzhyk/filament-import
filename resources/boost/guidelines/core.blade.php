## romansulzhyk/filament-import

- Excel (`.xlsx`) and CSV import for Filament 4 and 5 resources, synchronous, with no queue worker. Use `RomanSulzhyk\FilamentImport\Actions\ExcelImportAction`, not the eightynine plugin's class.
- Zero-config from `$fillable`, or `->importer(SomeImporter::class)` to run an existing Filament `Importer` class with `.xlsx` support.
- Files over `syncRowLimit` (default 2,000 rows) are refused; `.xls` is refused. Activate the `filament-import` skill for the full API and tests.
