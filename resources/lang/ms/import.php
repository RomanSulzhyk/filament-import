<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => 'Import :label',
        'submit' => 'Import',
        'download_template' => 'Muat turun templat',
    ],

    'steps' => [
        'upload' => 'Muat naik fail',
        'mapping' => 'Padankan lajur',
    ],

    'fields' => [
        'file' => [
            'label' => 'Hamparan',
            'helper' => 'Fail .xlsx atau .csv. Baris pertama yang tidak kosong mesti mengandungi pengepala lajur.',
        ],
        'mapping' => [
            'helper' => 'Pilih lajur dalam fail anda untuk setiap medan. Padanan telah dicadangkan secara automatik; biarkan medan kosong untuk melangkaunya.',
            'placeholder' => 'Jangan import',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import selesai',
            'body' => ':created dicipta, :updated dikemas kini, :failed gagal.',
            'body_with_skipped' => ':created dicipta, :updated dikemas kini, :skipped dilangkau, :failed gagal.',
        ],
        'failed' => [
            'title' => 'Import tidak dapat dimulakan',
        ],
        'download_failures' => 'Muat turun baris yang gagal',
    ],

    'errors' => [
        'too_many_rows' => 'Fail ini mempunyai lebih daripada :limit baris, iaitu had untuk import segera. Pecahkan fail, atau naikkan filament-import.sync_row_limit.',
        'unexpected' => 'Ralat tidak dijangka. Baris ini tidak diimport.',
        'unreadable' => 'Fail tidak dapat dibaca. Muat naik fail .xlsx atau .csv dengan baris pengepala.',
        'no_headers' => 'Fail tidak mempunyai baris pengepala.',
        'legacy_xls' => 'Ini ialah fail .xls format lama. Buka dalam Excel dan simpan sebagai .xlsx, kemudian muat naik semula.',
        'too_large_uncompressed' => 'Hamparan ini mengandungi terlalu banyak data selepas dinyahmampat untuk dibaca dengan selamat. Pecahkan kepada fail yang lebih kecil.',
    ],

    'failures_file' => [
        'row_column' => 'Baris',
        'errors_column' => 'Ralat',
    ],

];
