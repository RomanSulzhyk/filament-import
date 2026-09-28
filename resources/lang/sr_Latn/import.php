<?php

return [

    'action' => [
        'label' => 'Uvoz',
        'modal_heading' => 'Uvezi :label',
        'submit' => 'Uvezi',
        'download_template' => 'Preuzmi šablon',
    ],

    'steps' => [
        'upload' => 'Otpremi datoteku',
        'mapping' => 'Poveži kolone',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabela',
            'helper' => 'Datoteka .xlsx ili .csv. Njen prvi neprazan red mora da sadrži zaglavlja kolona.',
        ],
        'mapping' => [
            'helper' => 'Izaberite koja kolona vaše datoteke popunjava svako polje. Podudaranja su predložena automatski; ostavite polje prazno da biste ga preskočili.',
            'placeholder' => 'Ne uvozi',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Uvoz je završen',
            'body' => 'Kreirano: :created, ažurirano: :updated, neuspešno: :failed.',
            'body_with_skipped' => 'Kreirano: :created, ažurirano: :updated, preskočeno: :skipped, neuspešno: :failed.',
        ],
        'failed' => [
            'title' => 'Uvoz nije moguće pokrenuti',
        ],
        'download_failures' => 'Preuzmi neuspešne redove',
    ],

    'errors' => [
        'too_many_rows' => 'Ova datoteka ima više od :limit redova, što je ograničenje za trenutni uvoz. Podelite datoteku ili povećajte filament-import.sync_row_limit.',
        'unexpected' => 'Neočekivana greška. Red nije uvezen.',
        'unreadable' => 'Datoteku nije moguće pročitati. Otpremite datoteku .xlsx ili .csv sa redom zaglavlja.',
        'no_headers' => 'Datoteka nema red zaglavlja.',
        'legacy_xls' => 'Ovo je stara datoteka .xls. Otvorite je u Excel, sačuvajte kao .xlsx i ponovo je otpremite.',
        'too_large_uncompressed' => 'Ova tabela se raspakuje u previše podataka da bi se bezbedno pročitala. Podelite je na manje datoteke.',
    ],

    'failures_file' => [
        'row_column' => 'Red',
        'errors_column' => 'Greške',
    ],

];
