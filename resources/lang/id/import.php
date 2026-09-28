<?php

return [

    'action' => [
        'label' => 'Impor',
        'modal_heading' => 'Impor :label',
        'submit' => 'Impor',
        'download_template' => 'Unduh templat',
    ],

    'steps' => [
        'upload' => 'Unggah berkas',
        'mapping' => 'Cocokkan kolom',
    ],

    'fields' => [
        'file' => [
            'label' => 'Spreadsheet',
            'helper' => 'Berkas .xlsx atau .csv. Baris pertama yang tidak kosong harus berisi judul kolom.',
        ],
        'mapping' => [
            'helper' => 'Pilih kolom dalam berkas Anda untuk setiap bidang. Kecocokan sudah disarankan secara otomatis; biarkan bidang kosong untuk melewatinya.',
            'placeholder' => 'Jangan impor',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Impor selesai',
            'body' => ':created dibuat, :updated diperbarui, :failed gagal.',
            'body_with_skipped' => ':created dibuat, :updated diperbarui, :skipped dilewati, :failed gagal.',
        ],
        'failed' => [
            'title' => 'Impor tidak dapat dimulai',
        ],
        'download_failures' => 'Unduh baris yang gagal',
    ],

    'errors' => [
        'too_many_rows' => 'Berkas ini memiliki lebih dari :limit baris, batas untuk impor langsung. Pecah berkas, atau naikkan filament-import.sync_row_limit.',
        'unexpected' => 'Terjadi kesalahan tak terduga. Baris ini tidak diimpor.',
        'unreadable' => 'Berkas tidak dapat dibaca. Unggah berkas .xlsx atau .csv dengan baris judul.',
        'no_headers' => 'Berkas tidak memiliki baris judul.',
        'legacy_xls' => 'Ini adalah berkas .xls format lama. Buka di Excel dan simpan sebagai .xlsx, lalu unggah kembali.',
        'too_large_uncompressed' => 'Spreadsheet ini berisi terlalu banyak data setelah diekstrak untuk dibaca dengan aman. Pecah menjadi berkas yang lebih kecil.',
    ],

    'failures_file' => [
        'row_column' => 'Baris',
        'errors_column' => 'Kesalahan',
    ],

];
