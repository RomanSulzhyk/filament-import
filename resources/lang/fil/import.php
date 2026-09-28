<?php

return [

    'action' => [
        'label' => 'I-import',
        'modal_heading' => 'I-import ang :label',
        'submit' => 'I-import',
        'download_template' => 'I-download ang template',
    ],

    'steps' => [
        'upload' => 'Mag-upload ng file',
        'mapping' => 'Itugma ang mga column',
    ],

    'fields' => [
        'file' => [
            'label' => 'Spreadsheet',
            'helper' => 'Isang .xlsx o .csv file. Dapat nasa unang hindi blangkong row nito ang mga column header.',
        ],
        'mapping' => [
            'helper' => 'Piliin kung aling column ng iyong file ang pupuno sa bawat field. Awtomatikong iminungkahi ang mga tugma; iwanang blangko ang field para laktawan ito.',
            'placeholder' => 'Huwag i-import',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Tapos na ang pag-import',
            'body' => ':created ang nagawa, :updated ang na-update, :failed ang pumalya.',
            'body_with_skipped' => ':created ang nagawa, :updated ang na-update, :skipped ang nilaktawan, :failed ang pumalya.',
        ],
        'failed' => [
            'title' => 'Hindi masimulan ang pag-import',
        ],
        'download_failures' => 'I-download ang mga pumalyang row',
    ],

    'errors' => [
        'too_many_rows' => 'Mayroong higit sa :limit row ang file na ito, na siyang limitasyon para sa agarang pag-import. Hatiin ang file, o taasan ang filament-import.sync_row_limit.',
        'unexpected' => 'Hindi inaasahang error. Hindi na-import ang row.',
        'unreadable' => 'Hindi mabasa ang file. Mag-upload ng .xlsx o .csv file na may header row.',
        'no_headers' => 'Walang header row ang file.',
        'legacy_xls' => 'Isa itong lumang uri ng .xls file. Buksan ito sa Excel at i-save bilang .xlsx, pagkatapos ay i-upload ulit.',
        'too_large_uncompressed' => 'Masyadong malaki ang data ng spreadsheet na ito kapag binuksan para mabasa nang ligtas. Hatiin ito sa mas maliliit na file.',
    ],

    'failures_file' => [
        'row_column' => 'Row',
        'errors_column' => 'Mga error',
    ],

];
