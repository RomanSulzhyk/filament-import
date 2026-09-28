<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use InvalidArgumentException;
use ZipArchive;

class ReaderFactory
{
    public const EXTENSIONS = ['csv', 'txt', 'xlsx'];

    public static function make(string $path, ?string $originalName = null, ?string $csvEncoding = null): SpreadsheetReader
    {
        $magic = static::magic($path);

        // Legacy .xls is an OLE compound file. It must be refused here, or it
        // would be misread as a CSV full of binary garbage.
        if ($magic === "\xD0\xCF\x11\xE0") {
            throw new UnsupportedSpreadsheet(__('filament-import::import.errors.legacy_xls'));
        }

        $extension = strtolower(pathinfo($originalName ?? $path, PATHINFO_EXTENSION));

        if ($extension === 'xlsx' || $magic === "PK\x03\x04") {
            static::assertSafeArchive($path);

            return new XlsxReader($path);
        }

        if (in_array($extension, ['csv', 'tsv', 'txt'], true) || static::looksLikeText($path)) {
            return new CsvReader($path, encoding: $csvEncoding);
        }

        throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
    }

    /** Archives already checked in this process, so repeated opens in one request are free. */
    protected static array $checked = [];

    protected const MEMO_SIZE = 32;

    /**
     * An .xlsx is a ZIP archive. A small upload can expand to hundreds of
     * megabytes of XML and tie up a worker, so it is checked before OpenSpout
     * opens it.
     *
     * The sizes an archive declares about itself are written by the uploader
     * and cannot be trusted, so every entry, whatever its name, is actually
     * inflated and its bytes counted, stopping as soon as the limit is passed.
     * At most the configured limit is ever inflated. The ratio is measured
     * against the real file size. An archive that is not a workbook is refused
     * before anything is inflated.
     */
    public static function assertSafeArchive(string $path): void
    {
        clearstatcache(true, $path);
        $fileSize = (int) @filesize($path);
        $maxBytes = (int) config('filament-import.xlsx_max_uncompressed_bytes', 100 * 1024 * 1024);
        $maxRatio = (int) config('filament-import.xlsx_max_compression_ratio', 200);
        $key = implode('|', [$path, $fileSize, (int) @filemtime($path), $maxBytes, $maxRatio]);

        if (isset(static::$checked[$key])) {
            return;
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
        }

        try {
            if ($zip->locateName('xl/workbook.xml') === false) {
                throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
            }

            $limit = $fileSize > 0 ? min($maxBytes, $fileSize * $maxRatio) : $maxBytes;
            $total = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stream = $zip->getStreamIndex($i);

                if ($stream === false) {
                    throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
                }

                try {
                    while (! feof($stream)) {
                        // A corrupt entry raises a zlib warning, which Laravel would
                        // turn into a logged exception; it is a refusal, not an error.
                        $chunk = @fread($stream, 1 << 20);

                        if ($chunk === false) {
                            throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
                        }

                        $total += strlen($chunk);

                        if ($total > $limit) {
                            throw new UnsupportedSpreadsheet(__('filament-import::import.errors.too_large_uncompressed'));
                        }
                    }
                } finally {
                    fclose($stream);
                }
            }
        } finally {
            $zip->close();
        }

        if (count(static::$checked) >= static::MEMO_SIZE) {
            static::$checked = [];
        }

        static::$checked[$key] = true;
    }

    protected static function magic(string $path): string
    {
        $handle = @fopen($path, 'rb');

        if (! $handle) {
            throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
        }

        $magic = (string) fread($handle, 4);
        fclose($handle);

        return $magic;
    }

    /**
     * A file without a text extension is read as CSV only when its start
     * looks like text: a UTF-16 byte order mark, or no NUL and no control
     * characters other than tab, line feed and carriage return. So an image
     * or other binary file uploaded under a generic type is refused instead
     * of being read as a CSV full of garbage.
     */
    protected static function looksLikeText(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if (! $handle) {
            return false;
        }

        $sample = (string) fread($handle, 1024);
        fclose($handle);

        if ($sample === '') {
            return false;
        }

        if (str_starts_with($sample, "\xFF\xFE") || str_starts_with($sample, "\xFE\xFF")) {
            return true;
        }

        return preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $sample) === 0;
    }
}
