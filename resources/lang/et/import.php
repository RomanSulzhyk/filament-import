<?php

return [

    'action' => [
        'label' => 'Impordi',
        'modal_heading' => 'Impordi :label',
        'submit' => 'Impordi',
        'download_template' => 'Laadi mall alla',
    ],

    'steps' => [
        'upload' => 'Laadi fail üles',
        'mapping' => 'Seosta veerud',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabel',
            'helper' => 'Fail .xlsx või .csv. Selle esimene mittetühi rida peab sisaldama veergude päiseid.',
        ],
        'mapping' => [
            'helper' => 'Valige, milline teie faili veerg täidab iga välja. Vasted pakuti välja automaatselt; jätke väli tühjaks, et see vahele jätta.',
            'placeholder' => 'Ära impordi',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import lõpetatud',
            'body' => 'Loodud: :created, uuendatud: :updated, ebaõnnestunud: :failed.',
            'body_with_skipped' => 'Loodud: :created, uuendatud: :updated, vahele jäetud: :skipped, ebaõnnestunud: :failed.',
        ],
        'failed' => [
            'title' => 'Importi ei saanud alustada',
        ],
        'download_failures' => 'Laadi alla ebaõnnestunud read',
    ],

    'errors' => [
        'too_many_rows' => 'Selles failis on rohkem kui :limit rida, mis on kohese impordi piirang. Jagage fail osadeks või suurendage filament-import.sync_row_limit väärtust.',
        'unexpected' => 'Ootamatu viga. Rida ei imporditud.',
        'unreadable' => 'Faili ei õnnestunud lugeda. Laadige üles päisereaga .xlsx- või .csv-fail.',
        'no_headers' => 'Failil puudub päiserida.',
        'legacy_xls' => 'See on vana tüüpi .xls-fail. Avage see Excelis, salvestage .xlsx-vormingus ja laadige uuesti üles.',
        'too_large_uncompressed' => 'See tabel laieneb lahtipakkimisel liiga suureks andmemahuks, et seda turvaliselt lugeda. Jagage see väiksemateks failideks.',
    ],

    'failures_file' => [
        'row_column' => 'Rida',
        'errors_column' => 'Vead',
    ],

];
