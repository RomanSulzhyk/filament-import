<?php

return [

    'action' => [
        'label' => 'Uvozi',
        'modal_heading' => 'Uvozi :label',
        'submit' => 'Uvozi',
        'download_template' => 'Prenesi predlogo',
    ],

    'steps' => [
        'upload' => 'Naloži datoteko',
        'mapping' => 'Poveži stolpce',
    ],

    'fields' => [
        'file' => [
            'label' => 'Preglednica',
            'helper' => 'Datoteka .xlsx ali .csv. Njena prva neprazna vrstica mora vsebovati glave stolpcev.',
        ],
        'mapping' => [
            'helper' => 'Izberite, kateri stolpec vaše datoteke napolni posamezno polje. Ujemanja so bila predlagana samodejno; polje pustite prazno, če ga želite preskočiti.',
            'placeholder' => 'Ne uvozi',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Uvoz zaključen',
            'body' => 'Ustvarjeno: :created, posodobljeno: :updated, neuspešno: :failed.',
            'body_with_skipped' => 'Ustvarjeno: :created, posodobljeno: :updated, preskočeno: :skipped, neuspešno: :failed.',
        ],
        'failed' => [
            'title' => 'Uvoza ni bilo mogoče začeti',
        ],
        'download_failures' => 'Prenesi neuspešne vrstice',
    ],

    'errors' => [
        'too_many_rows' => 'Ta datoteka ima več kot :limit vrstic, kar je omejitev za takojšnji uvoz. Razdelite datoteko ali povečajte filament-import.sync_row_limit.',
        'unexpected' => 'Nepričakovana napaka. Vrstica ni bila uvožena.',
        'unreadable' => 'Datoteke ni bilo mogoče prebrati. Naložite datoteko .xlsx ali .csv z vrstico glav.',
        'no_headers' => 'Datoteka nima vrstice glav.',
        'legacy_xls' => 'To je starejša datoteka .xls. Odprite jo v Excel, jo shranite kot .xlsx in jo znova naložite.',
        'too_large_uncompressed' => 'Ta preglednica se razširi v preveč podatkov, da bi jo bilo mogoče varno prebrati. Razdelite jo na manjše datoteke.',
    ],

    'failures_file' => [
        'row_column' => 'Vrstica',
        'errors_column' => 'Napake',
    ],

];
