<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => 'Import :label',
        'submit' => 'Import',
    ],

    'steps' => [
        'upload' => 'Upload file',
        'mapping' => 'Match columns',
    ],

    'fields' => [
        'file' => [
            'label' => 'Spreadsheet',
            'helper' => 'An .xlsx or .csv file. Its first non-empty row must contain the column headers.',
        ],
        'mapping' => [
            'helper' => 'Choose which column of your file feeds each field. Matches were suggested automatically; leave a field empty to skip it.',
            'placeholder' => 'Do not import',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import finished',
            'body' => ':created created, :updated updated, :failed failed.',
            'body_with_skipped' => ':created created, :updated updated, :skipped skipped, :failed failed.',
        ],
        'failed' => [
            'title' => 'Import could not start',
        ],
        'download_failures' => 'Download failed rows',
    ],

    'errors' => [
        'too_many_rows' => 'This file has more than :limit rows, which is the limit for an immediate import. Split the file, or raise filament-import.sync_row_limit.',
        'unexpected' => 'Unexpected error. The row was not imported.',
        'unreadable' => 'The file could not be read. Upload an .xlsx or .csv file with a header row.',
        'no_headers' => 'The file has no header row.',
        'legacy_xls' => 'This is an old-style .xls file. Open it in Excel and save it as .xlsx, then upload it again.',
        'too_large_uncompressed' => 'This spreadsheet expands to too much data to read safely. Split it into smaller files.',
    ],

    'failures_file' => [
        'row_column' => 'Row',
        'errors_column' => 'Errors',
    ],

];
