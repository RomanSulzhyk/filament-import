<?php

return [

    'action' => [
        'label' => 'Importér',
        'modal_heading' => 'Importér :label',
        'submit' => 'Importér',
        'download_template' => 'Last ned mal',
    ],

    'steps' => [
        'upload' => 'Last opp fil',
        'mapping' => 'Koble kolonner',
    ],

    'fields' => [
        'file' => [
            'label' => 'Regneark',
            'helper' => 'En .xlsx- eller .csv-fil. Den første raden som ikke er tom, må inneholde kolonneoverskriftene.',
        ],
        'mapping' => [
            'helper' => 'Velg hvilken kolonne i filen som fyller hvert felt. Koblinger er foreslått automatisk; la et felt stå tomt for å hoppe over det.',
            'placeholder' => 'Ikke importér',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importering gjennomført',
            'body' => ':created opprettet, :updated oppdatert, :failed feilet.',
            'body_with_skipped' => ':created opprettet, :updated oppdatert, :skipped hoppet over, :failed feilet.',
        ],
        'failed' => [
            'title' => 'Importeringen kunne ikke starte',
        ],
        'download_failures' => 'Last ned rader som feilet',
    ],

    'errors' => [
        'too_many_rows' => 'Filen har mer enn :limit rader, som er grensen for en umiddelbar importering. Del opp filen, eller øk filament-import.sync_row_limit.',
        'unexpected' => 'Uventet feil. Raden ble ikke importert.',
        'unreadable' => 'Filen kunne ikke leses. Last opp en .xlsx- eller .csv-fil med en overskriftsrad.',
        'no_headers' => 'Filen har ingen overskriftsrad.',
        'legacy_xls' => 'Dette er en .xls-fil i gammelt format. Åpne den i Excel, lagre den som .xlsx og last den opp på nytt.',
        'too_large_uncompressed' => 'Utpakket inneholder dette regnearket for mye data til å leses trygt. Del det opp i mindre filer.',
    ],

    'failures_file' => [
        'row_column' => 'Rad',
        'errors_column' => 'Feil',
    ],

];
