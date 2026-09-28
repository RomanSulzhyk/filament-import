<?php

namespace RomanSulzhyk\FilamentImport\Importing;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class FailedRowsWriter
{
    /**
     * Writes failed rows as an .xlsx the user can open, fix and re-upload:
     * the original columns first, then the file row number and the reasons.
     *
     * Every cell is written as an explicit text cell. A value such as
     * `=HYPERLINK(...)` is shown as text instead of running as a formula, and
     * it is read back unchanged on re-upload. A CSV cannot do both: prefixing
     * an apostrophe makes it safe but corrupts values like "+15550101".
     *
     * @param  list<string>  $headers
     * @param  list<RowFailure>  $failures
     * @return string Path on the configured disk.
     */
    public static function write(array $headers, array $failures): string
    {
        // Housekeeping must never fail an import whose rows are already saved.
        // A disk that refuses listing or deletion, or two imports racing on the
        // same expired file, is reported and left to filament-import:prune.
        try {
            static::prune();
        } catch (Throwable $e) {
            report($e);
        }

        $headers = static::withoutPreviousReportColumns($headers);
        $local = tempnam(sys_get_temp_dir(), 'filament-import-failures-');

        try {
            static::writeLocal($local, $headers, $failures);

            $path = static::directory() . '/' . Str::uuid() . '.xlsx';
            $stream = fopen($local, 'rb');

            try {
                if (Storage::disk(static::disk())->writeStream($path, $stream) === false) {
                    throw new \RuntimeException("The failed-rows report could not be written to [{$path}].");
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } finally {
            @unlink($local);
        }

        return $path;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<RowFailure>  $failures
     */
    protected static function writeLocal(string $local, array $headers, array $failures): void
    {
        $writer = new Writer;
        $writer->openToFile($local);

        try {
            $writer->addRow(static::textRow([
                ...$headers,
                __('filament-import::import.failures_file.row_column'),
                __('filament-import::import.failures_file.errors_column'),
            ]));

            foreach ($failures as $failure) {
                $writer->addRow(static::textRow([
                    ...array_map(fn ($header) => $failure->data[$header] ?? '', $headers),
                    (string) $failure->row,
                    implode(' ', $failure->messages),
                ]));
            }
        } finally {
            $writer->close();
        }
    }

    /**
     * A user who fixes a report and uploads it again brings its row and
     * errors columns along. They are dropped, so a second report does not
     * grow "Row_2" and "Errors_2" columns full of stale reasons. Only a pair
     * is dropped: a lone "Errors" column in the user's own file is kept.
     *
     * @param  list<string>  $headers
     * @return list<string>
     */
    public static function withoutPreviousReportColumns(array $headers): array
    {
        [$rowNames, $errorNames] = static::reportColumnNames();
        $base = fn (string $header) => mb_strtolower(trim(preg_replace('/_\d+$/', '', $header)));

        $rows = array_filter($headers, fn ($header) => in_array($base($header), $rowNames, true));
        $errors = array_filter($headers, fn ($header) => in_array($base($header), $errorNames, true));

        if ($rows === [] || $errors === []) {
            return $headers;
        }

        return array_values(array_diff($headers, $rows, $errors));
    }

    /** @var array{0: list<string>, 1: list<string>}|null */
    protected static ?array $reportColumnNames = null;

    /**
     * The report's column names in every language, since the user who
     * re-uploads it may have downloaded it in another locale.
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    protected static function reportColumnNames(): array
    {
        if (static::$reportColumnNames === null) {
            $rows = [];
            $errors = [];

            foreach (glob(dirname(__DIR__, 2).'/resources/lang/*/import.php') ?: [] as $file) {
                $strings = require $file;
                $rows[] = mb_strtolower($strings['failures_file']['row_column'] ?? '');
                $errors[] = mb_strtolower($strings['failures_file']['errors_column'] ?? '');
            }

            static::$reportColumnNames = [$rows, $errors];
        }

        [$rows, $errors] = static::$reportColumnNames;

        // Published or app-level overrides for the current request's locale,
        // read every time: under Octane the locale changes between requests.
        $rows[] = mb_strtolower(__('filament-import::import.failures_file.row_column'));
        $errors[] = mb_strtolower(__('filament-import::import.failures_file.errors_column'));

        return [
            array_values(array_unique(array_filter($rows))),
            array_values(array_unique(array_filter($errors))),
        ];
    }

    /**
     * Deletes reports older than the configured lifetime. Their download links
     * have already expired, and they hold the user's rejected customer rows.
     *
     * @return int Number of files deleted.
     */
    public static function prune(): int
    {
        $disk = Storage::disk(static::disk());
        $cutoff = now()->subMinutes(static::ttlMinutes())->getTimestamp();
        $deleted = 0;

        foreach ($disk->files(static::directory()) as $file) {
            if (preg_match('/\.(xlsx|csv)$/', $file) !== 1) {
                continue;
            }

            if ($disk->lastModified($file) < $cutoff && $disk->delete($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function ttlMinutes(): int
    {
        return max(1, (int) config('filament-import.failed_rows_ttl_minutes', 1440));
    }

    public static function disk(): string
    {
        return (string) config('filament-import.disk', 'local');
    }

    public static function directory(): string
    {
        return trim((string) config('filament-import.directory', 'filament-import'), '/') . '/failures';
    }

    /**
     * @param  list<mixed>  $values
     */
    protected static function textRow(array $values): Row
    {
        return new Row(array_map(
            fn ($value) => new StringCell((string) $value, null),
            $values,
        ));
    }
}
