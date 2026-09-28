<?php

return [

    'action' => [
        'label' => 'Uvoz',
        'modal_heading' => 'Uvezi :label',
        'submit' => 'Uvezi',
        'download_template' => 'Preuzmi predložak',
    ],

    'steps' => [
        'upload' => 'Učitaj datoteku',
        'mapping' => 'Poveži stupce',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tablica',
            'helper' => 'Datoteka .xlsx ili .csv. Njezin prvi neprazni red mora sadržavati zaglavlja stupaca.',
        ],
        'mapping' => [
            'helper' => 'Odaberite koji stupac vaše datoteke popunjava pojedino polje. Podudaranja su predložena automatski; ostavite polje praznim da ga preskočite.',
            'placeholder' => 'Ne uvozi',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Uvoz je završen',
            'body' => 'Stvoreno: :created, ažurirano: :updated, neuspješno: :failed.',
            'body_with_skipped' => 'Stvoreno: :created, ažurirano: :updated, preskočeno: :skipped, neuspješno: :failed.',
        ],
        'failed' => [
            'title' => 'Uvoz nije moguće pokrenuti',
        ],
        'download_failures' => 'Preuzmi neuspjele redove',
    ],

    'errors' => [
        'too_many_rows' => 'Ova datoteka ima više od :limit redova, što je ograničenje za trenutačni uvoz. Podijelite datoteku ili povećajte filament-import.sync_row_limit.',
        'unexpected' => 'Neočekivana pogreška. Red nije uvezen.',
        'unreadable' => 'Datoteku nije moguće pročitati. Učitajte datoteku .xlsx ili .csv s redom zaglavlja.',
        'no_headers' => 'Datoteka nema red zaglavlja.',
        'legacy_xls' => 'Ovo je stara datoteka .xls. Otvorite je u Excel, spremite kao .xlsx i ponovno je učitajte.',
        'too_large_uncompressed' => 'Ova se tablica raspakira u previše podataka da bi se sigurno pročitala. Podijelite je na manje datoteke.',
    ],

    'failures_file' => [
        'row_column' => 'Red',
        'errors_column' => 'Pogreške',
    ],

];
