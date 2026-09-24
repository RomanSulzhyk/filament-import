<?php

return [

    /*
     * Files with at most this many data rows are imported immediately, inside
     * the request, with no queue worker required. Larger files are refused
     * before any row is written, rather than risking a request timeout.
     */
    'sync_row_limit' => 2000,

    /*
     * Disk and directory for failed-row reports. The disk must be private:
     * these files contain the rejected rows of the user's spreadsheet.
     */
    'disk' => env('FILAMENT_IMPORT_DISK', 'local'),

    'directory' => 'filament-import',

    /*
     * Minutes a failed-rows report is kept. Its download link expires at the
     * same time, and older reports are deleted whenever a new one is written.
     * Run `php artisan filament-import:prune` on a schedule as well if imports
     * are rare, so old reports do not wait for the next import to be removed.
     */
    'failed_rows_ttl_minutes' => 1440,

    /*
     * Minimum similarity (0-1) for a misspelled header ("Adress") to be
     * auto-matched. Only near-identical spellings should pass.
     */
    'match_threshold' => 0.85,

    /*
     * An .xlsx is a ZIP archive. These limits refuse files whose contents
     * would expand far beyond their upload size before they are parsed.
     */
    'xlsx_max_uncompressed_bytes' => 100 * 1024 * 1024,

    'xlsx_max_compression_ratio' => 200,

];
