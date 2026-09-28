<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => 'Importujte :label',
        'submit' => 'Importujte',
        'download_template' => 'Preuzmi šablon',
    ],

    'steps' => [
        'upload' => 'Učitajte fajl',
        'mapping' => 'Povežite kolone',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabela',
            'helper' => 'Fajl .xlsx ili .csv. Njegov prvi neprazni red mora sadržavati zaglavlja kolona.',
        ],
        'mapping' => [
            'helper' => 'Odaberite koja kolona vašeg fajla popunjava svako polje. Podudaranja su predložena automatski; ostavite polje prazno da ga preskočite.',
            'placeholder' => 'Ne importuj',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import završen',
            'body' => 'Kreirano: :created, ažurirano: :updated, neuspješno: :failed.',
            'body_with_skipped' => 'Kreirano: :created, ažurirano: :updated, preskočeno: :skipped, neuspješno: :failed.',
        ],
        'failed' => [
            'title' => 'Import nije moguće pokrenuti',
        ],
        'download_failures' => 'Preuzmite neuspjele redove',
    ],

    'errors' => [
        'too_many_rows' => 'Ovaj fajl ima više od :limit redova, što je ograničenje za trenutni import. Podijelite fajl ili povećajte filament-import.sync_row_limit.',
        'unexpected' => 'Neočekivana greška. Red nije importovan.',
        'unreadable' => 'Fajl nije moguće pročitati. Učitajte fajl .xlsx ili .csv sa redom zaglavlja.',
        'no_headers' => 'Fajl nema red zaglavlja.',
        'legacy_xls' => 'Ovo je stari fajl .xls. Otvorite ga u Excel, sačuvajte kao .xlsx i ponovo ga učitajte.',
        'too_large_uncompressed' => 'Ova tabela se raspakuje u previše podataka da bi se sigurno pročitala. Podijelite je na manje fajlove.',
    ],

    'failures_file' => [
        'row_column' => 'Red',
        'errors_column' => 'Greške',
    ],

];
