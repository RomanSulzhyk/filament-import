<?php

return [

    'action' => [
        'label' => 'Lâkluhna',
        'modal_heading' => ':label lâkluhna',
        'submit' => 'Lâkluhna',
        'download_template' => 'Template download rawh',
    ],

    'steps' => [
        'upload' => 'File upload rawh',
        'mapping' => 'Column te inmilpui rawh',
    ],

    'fields' => [
        'file' => [
            'label' => 'Spreadsheet',
            'helper' => '.xlsx emaw .csv file. A row ruak lo hmasa berah column header te a awm tur a ni.',
        ],
        'mapping' => [
            'helper' => 'I file-a column tu nge field tin atana hman tur thlang rawh. A inmil te chu automatic in rawtna siam a ni tawh; field i kalsan duh chuan a ruakin dah rawh.',
            'placeholder' => 'Lâkluh suh',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Lâkluh a zo e',
            'body' => ':created siam, :updated tihdanglam, :failed hlawhchham.',
            'body_with_skipped' => ':created siam, :updated tihdanglam, :skipped kalsan, :failed hlawhchham.',
        ],
        'failed' => [
            'title' => 'Lâkluh a intan thei lo',
        ],
        'download_failures' => 'Row hlawhchham te download rawh',
    ],

    'errors' => [
        'too_many_rows' => 'He file hian row :limit aia tam a nei a, chu chu vawikhata lâkluh theih zat tam ber a ni. File chu then rawh, emaw filament-import.sync_row_limit tihsan rawh.',
        'unexpected' => 'Error beisei loh a awm. He row hi lâkluh a ni lo.',
        'unreadable' => 'File chhiar theih a ni lo. Header row nei .xlsx emaw .csv file upload rawh.',
        'no_headers' => 'File hian header row a nei lo.',
        'legacy_xls' => 'Hei hi .xls file hlui a ni. Excel-ah hawng la .xlsx angin save la, upload leh rawh.',
        'too_large_uncompressed' => 'He spreadsheet hi a data a tam lutuk avangin himtakin chhiar theih a ni lo. File te tea then rawh.',
    ],

    'failures_file' => [
        'row_column' => 'Row',
        'errors_column' => 'Error te',
    ],

];
