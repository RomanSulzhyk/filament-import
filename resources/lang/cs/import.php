<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => 'Import :label',
        'submit' => 'Importovat',
        'download_template' => 'Stáhnout šablonu',
    ],

    'steps' => [
        'upload' => 'Nahrát soubor',
        'mapping' => 'Přiřadit sloupce',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabulka',
            'helper' => 'Soubor .xlsx nebo .csv. Jeho první neprázdný řádek musí obsahovat záhlaví sloupců.',
        ],
        'mapping' => [
            'helper' => 'Vyberte, který sloupec vašeho souboru plní jednotlivá pole. Přiřazení byla navržena automaticky; pole, které chcete přeskočit, ponechte prázdné.',
            'placeholder' => 'Neimportovat',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import dokončen',
            'body' => 'Vytvořeno: :created, aktualizováno: :updated, selhalo: :failed.',
            'body_with_skipped' => 'Vytvořeno: :created, aktualizováno: :updated, přeskočeno: :skipped, selhalo: :failed.',
        ],
        'failed' => [
            'title' => 'Import nelze spustit',
        ],
        'download_failures' => 'Stáhnout neúspěšné řádky',
    ],

    'errors' => [
        'too_many_rows' => 'Tento soubor má více než :limit řádků, což je limit pro okamžitý import. Rozdělte soubor nebo zvyšte filament-import.sync_row_limit.',
        'unexpected' => 'Neočekávaná chyba. Řádek nebyl importován.',
        'unreadable' => 'Soubor nelze přečíst. Nahrajte soubor .xlsx nebo .csv s řádkem záhlaví.',
        'no_headers' => 'Soubor nemá řádek záhlaví.',
        'legacy_xls' => 'Toto je starší soubor .xls. Otevřete jej v Excel, uložte jako .xlsx a nahrajte znovu.',
        'too_large_uncompressed' => 'Tato tabulka se rozbalí na příliš velký objem dat, než aby ji bylo možné bezpečně přečíst. Rozdělte ji na menší soubory.',
    ],

    'failures_file' => [
        'row_column' => 'Řádek',
        'errors_column' => 'Chyby',
    ],

];
