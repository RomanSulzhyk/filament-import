<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use InvalidArgumentException;
use ZipArchive;

class ReaderFactory
{
    public const EXTENSIONS = ['csv', 'txt', 'xlsx'];

    public static function make(string $path, ?string $originalName = null): SpreadsheetReader
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

        if (in_array($extension, ['csv', 'txt'], true) || ($magic !== '' && ! str_contains($magic, "\0"))) {
            return new CsvReader($path);
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
}
