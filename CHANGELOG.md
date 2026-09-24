# Changelog

All notable changes are recorded here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versions follow [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-09-24

First public release.

- `ExcelImportAction` imports `.xlsx` and `.csv` files into any Filament v4 or v5 resource, in the request, with no queue worker.
- Zero-config mode builds import fields from the model's `$fillable`, with `validateUsing()`, `upsertBy()`, `importColumns()` and `exceptColumns()`.
- Existing Filament `Importer` classes run unchanged through `->importer()`, including relationships, casting and lifecycle hooks.
- File headers are matched to fields automatically, including common field names in ten languages, and the user can correct every match.
- Each row runs in its own transaction on the model's connection. Failures carry the file's line number and come back as a signed, user-bound `.xlsx` report.
- CSV encoding (UTF-8, Windows-1251, Windows-1252) and delimiter are detected from the file.
- `.xlsx` archives are inflated and measured before parsing, and old `.xls` files are refused with a request to save them as `.xlsx`.
- Tenant ownership keys are never offered on tenant-scoped resources.
- English and Ukrainian translations.
