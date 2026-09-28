# Changelog

All notable changes are recorded here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versions follow [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-09-28

### Added
- Header matching and interface translations for every locale Filament ships (63 locales), except Russian. Synonyms now live in one file per language under `resources/synonyms`.
- CSV encoding detection for UTF-16 with a byte order mark, as Excel writes it.
- CSV encoding detection for the Excel code page of the app's locale: Windows-1250, 1253 to 1257, 874, Shift_JIS, GBK, Big5 and Korean. Windows-1258 can be named with `->csvEncoding()` but is not detected. Code pages mbstring lacks are decoded from bundled tables, so no iconv is needed.
- `->csvEncoding()` and the `csv_encoding` config key to name the CSV encoding instead of detecting it.
- An importer class's `getOptionsFormComponents()` fields are shown in the modal and reach it as `$this->options`, as in the core `ImportAction`. Before, they were silently ignored.
- `->optionsFormComponents()` adds fields to the modal, and `->additionalData()` writes fixed values to every record in zero-config mode, never overridden by the file. An `upsertBy()` key among them also finds the record to update, and the tenant ownership key is refused.
- A [Laravel Boost](https://github.com/laravel/boost) skill (`resources/boost/skills/filament-import`) and a short guideline, so AI coding agents in apps that select this package in `boost:install` or `boost:update` use its real API. A test keeps every method named in the skill's code samples real.
- A `context7.json` for the Context7 documentation index, a "Which import tool should I use?" comparison and an FAQ in the README.
- A "Download template" link in the modal: an `.xlsx` with one header per import column and the columns' examples. `->templateDownload(false)` hides it.

### Fixed
- Uploading a fixed failed-rows report again no longer adds `Row_2` and `Errors_2` columns with stale reasons to the next report.
- The temporary file of a failed-rows report is deleted even when writing it fails, and the report is streamed to the disk instead of read into memory.

### Changed
- Header normalization keeps combining marks, compares full-width and half-width forms as equal (with ext-intl), and only transliterates Latin, Cyrillic and Greek words whose every letter has a Latin form. Other scripts are compared as written, so unrelated words no longer meet after a lossy transliteration.
- Bilingual headers such as `Email (メール)` or `Name / 名前` match through either half.
- A header that means "first name" but is often used for a full name (`Ім'я`, `Nombre`, `Nome`, `이름` and their equivalents) fills a first-name field only when the file also has a surname column, and a full-name field only when it does not. A model with both fields gets no suggestion, as before for `Ім'я`. In 1.0.0 `Nombre` and `Nome` were suggested for a full-name field even then.
- The CSV code page of the app's locale is only used on strong evidence: the letters that differ from Windows-1252 must belong to the app's language and must not also read as a Western language. Western files therefore keep reading as Windows-1252; a file that could be either is read as Windows-1252, as in 1.0.0.
- New optional parameters: `CsvReader::__construct()` (`$encoding`), `CsvReader::detectEncoding()` (`$locale`), `ReaderFactory::make()` (`$csvEncoding`) and `FillableImporter::using()` (`$fixedValues`). A subclass that overrides one of these methods must add the parameter.

### Removed
- Russian field names from header matching (12 words). Words shared with Bulgarian, Macedonian and Serbian still match.
- The `HeaderSynonyms::GROUPS` constant, an undocumented internal. Use `HeaderSynonyms::KEYS` and `HeaderSynonyms::groups()`.

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
