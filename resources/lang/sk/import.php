<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => 'Import :label',
        'submit' => 'Importovať',
        'download_template' => 'Stiahnuť šablónu',
    ],

    'steps' => [
        'upload' => 'Nahrať súbor',
        'mapping' => 'Priradiť stĺpce',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabuľka',
            'helper' => 'Súbor .xlsx alebo .csv. Jeho prvý neprázdny riadok musí obsahovať hlavičky stĺpcov.',
        ],
        'mapping' => [
            'helper' => 'Vyberte, ktorý stĺpec vášho súboru plní jednotlivé polia. Priradenia boli navrhnuté automaticky; pole, ktoré chcete preskočiť, nechajte prázdne.',
            'placeholder' => 'Neimportovať',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import dokončený',
            'body' => 'Vytvorené: :created, aktualizované: :updated, zlyhané: :failed.',
            'body_with_skipped' => 'Vytvorené: :created, aktualizované: :updated, preskočené: :skipped, zlyhané: :failed.',
        ],
        'failed' => [
            'title' => 'Import sa nepodarilo spustiť',
        ],
        'download_failures' => 'Stiahnuť neúspešné riadky',
    ],

    'errors' => [
        'too_many_rows' => 'Tento súbor má viac ako :limit riadkov, čo je limit pre okamžitý import. Rozdeľte súbor alebo zvýšte filament-import.sync_row_limit.',
        'unexpected' => 'Neočakávaná chyba. Riadok nebol importovaný.',
        'unreadable' => 'Súbor sa nepodarilo prečítať. Nahrajte súbor .xlsx alebo .csv s riadkom hlavičiek.',
        'no_headers' => 'Súbor nemá riadok hlavičiek.',
        'legacy_xls' => 'Toto je starší súbor .xls. Otvorte ho v Excel, uložte ako .xlsx a nahrajte ho znova.',
        'too_large_uncompressed' => 'Táto tabuľka sa rozbalí na príliš veľký objem dát, aby sa dala bezpečne prečítať. Rozdeľte ju na menšie súbory.',
    ],

    'failures_file' => [
        'row_column' => 'Riadok',
        'errors_column' => 'Chyby',
    ],

];
