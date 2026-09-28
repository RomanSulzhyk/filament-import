<?php

return [

    'action' => [
        'label' => 'Importo',
        'modal_heading' => 'Importo :label',
        'submit' => 'Importo',
        'download_template' => 'Shkarko shabllonin',
    ],

    'steps' => [
        'upload' => 'Ngarko skedarin',
        'mapping' => 'Përputh kolonat',
    ],

    'fields' => [
        'file' => [
            'label' => 'Fletëllogaritëse',
            'helper' => 'Një skedar .xlsx ose .csv. Rreshti i tij i parë jo bosh duhet të përmbajë titujt e kolonave.',
        ],
        'mapping' => [
            'helper' => 'Zgjidhni cila kolonë e skedarit tuaj plotëson secilën fushë. Përputhjet u sugjeruan automatikisht; lëreni një fushë bosh për ta anashkaluar.',
            'placeholder' => 'Mos importo',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importimi përfundoi',
            'body' => 'Të krijuara: :created, të përditësuara: :updated, të dështuara: :failed.',
            'body_with_skipped' => 'Të krijuara: :created, të përditësuara: :updated, të anashkaluara: :skipped, të dështuara: :failed.',
        ],
        'failed' => [
            'title' => 'Importimi nuk mund të fillonte',
        ],
        'download_failures' => 'Shkarko rreshtat e dështuar',
    ],

    'errors' => [
        'too_many_rows' => 'Ky skedar ka më shumë se :limit rreshta, që është kufiri për një importim të menjëhershëm. Ndajeni skedarin ose rritni filament-import.sync_row_limit.',
        'unexpected' => 'Gabim i papritur. Rreshti nuk u importua.',
        'unreadable' => 'Skedari nuk mund të lexohej. Ngarkoni një skedar .xlsx ose .csv me rresht titujsh.',
        'no_headers' => 'Skedari nuk ka rresht titujsh.',
        'legacy_xls' => 'Ky është një skedar i vjetër .xls. Hapeni në Excel, ruajeni si .xlsx dhe ngarkojeni përsëri.',
        'too_large_uncompressed' => 'Kjo fletëllogaritëse zgjerohet në shumë të dhëna për t\'u lexuar në mënyrë të sigurt. Ndajeni në skedarë më të vegjël.',
    ],

    'failures_file' => [
        'row_column' => 'Rreshti',
        'errors_column' => 'Gabimet',
    ],

];
