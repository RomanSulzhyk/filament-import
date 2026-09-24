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

        $local = tempnam(sys_get_temp_dir(), 'filament-import-failures-');
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

        $path = static::directory() . '/' . Str::uuid() . '.xlsx';

        try {
            Storage::disk(static::disk())->put($path, (string) file_get_contents($local));
        } finally {
            @unlink($local);
        }

        return $path;
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
