<?php

return [

    'action' => [
        'label' => 'Importálás',
        'modal_heading' => ':label importálása',
        'submit' => 'Importálás',
        'download_template' => 'Sablon letöltése',
    ],

    'steps' => [
        'upload' => 'Fájl feltöltése',
        'mapping' => 'Oszlopok hozzárendelése',
    ],

    'fields' => [
        'file' => [
            'label' => 'Táblázat',
            'helper' => 'Egy .xlsx vagy .csv fájl. Az első nem üres sorának az oszlopfejléceket kell tartalmaznia.',
        ],
        'mapping' => [
            'helper' => 'Válassza ki, hogy a fájl melyik oszlopa tölti ki az egyes mezőket. Az egyezéseket automatikusan javasoltuk; hagyjon üresen egy mezőt, ha ki szeretné hagyni.',
            'placeholder' => 'Nincs importálás',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importálás befejezve',
            'body' => 'Létrehozva: :created, frissítve: :updated, sikertelen: :failed.',
            'body_with_skipped' => 'Létrehozva: :created, frissítve: :updated, kihagyva: :skipped, sikertelen: :failed.',
        ],
        'failed' => [
            'title' => 'Az importálás nem indítható el',
        ],
        'download_failures' => 'Sikertelen sorok letöltése',
    ],

    'errors' => [
        'too_many_rows' => 'Ez a fájl több mint :limit sort tartalmaz, ami az azonnali importálás korlátja. Ossza fel a fájlt, vagy növelje a filament-import.sync_row_limit értékét.',
        'unexpected' => 'Váratlan hiba. A sor nem lett importálva.',
        'unreadable' => 'A fájl nem olvasható. Töltsön fel egy fejlécsort tartalmazó .xlsx vagy .csv fájlt.',
        'no_headers' => 'A fájlnak nincs fejlécsora.',
        'legacy_xls' => 'Ez egy régi típusú .xls fájl. Nyissa meg Excel programban, mentse .xlsx formátumban, majd töltse fel újra.',
        'too_large_uncompressed' => 'Ez a táblázat kicsomagolva túl sok adatot tartalmaz ahhoz, hogy biztonságosan beolvasható legyen. Ossza fel kisebb fájlokra.',
    ],

    'failures_file' => [
        'row_column' => 'Sor',
        'errors_column' => 'Hibák',
    ],

];
