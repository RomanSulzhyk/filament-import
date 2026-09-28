<?php

return [

    'action' => [
        'label' => 'Importer',
        'modal_heading' => 'Importer :label',
        'submit' => 'Importer',
        'download_template' => 'Download skabelon',
    ],

    'steps' => [
        'upload' => 'Upload fil',
        'mapping' => 'Tilknyt kolonner',
    ],

    'fields' => [
        'file' => [
            'label' => 'Regneark',
            'helper' => 'En .xlsx- eller .csv-fil. Den første ikke-tomme række skal indeholde kolonneoverskrifterne.',
        ],
        'mapping' => [
            'helper' => 'Vælg, hvilken kolonne i din fil der udfylder hvert felt. Tilknytninger er foreslået automatisk; lad et felt stå tomt for at springe det over.',
            'placeholder' => 'Importér ikke',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import afsluttet',
            'body' => ':created oprettet, :updated opdateret, :failed fejlet.',
            'body_with_skipped' => ':created oprettet, :updated opdateret, :skipped sprunget over, :failed fejlet.',
        ],
        'failed' => [
            'title' => 'Importen kunne ikke starte',
        ],
        'download_failures' => 'Download rækker med fejl',
    ],

    'errors' => [
        'too_many_rows' => 'Filen har mere end :limit rækker, som er grænsen for en øjeblikkelig import. Del filen op, eller hæv filament-import.sync_row_limit.',
        'unexpected' => 'Uventet fejl. Rækken blev ikke importeret.',
        'unreadable' => 'Filen kunne ikke læses. Upload en .xlsx- eller .csv-fil med en overskriftsrække.',
        'no_headers' => 'Filen har ingen overskriftsrække.',
        'legacy_xls' => 'Dette er en .xls-fil i det gamle format. Åbn den i Excel, gem den som .xlsx, og upload den igen.',
        'too_large_uncompressed' => 'Udpakket fylder dette regneark for meget til at kunne læses sikkert. Del det op i mindre filer.',
    ],

    'failures_file' => [
        'row_column' => 'Række',
        'errors_column' => 'Fejl',
    ],

];
