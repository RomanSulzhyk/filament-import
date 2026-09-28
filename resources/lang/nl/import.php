<?php

return [

    'action' => [
        'label' => 'Importeren',
        'modal_heading' => ':label importeren',
        'submit' => 'Importeren',
        'download_template' => 'Sjabloon downloaden',
    ],

    'steps' => [
        'upload' => 'Bestand uploaden',
        'mapping' => 'Kolommen koppelen',
    ],

    'fields' => [
        'file' => [
            'label' => 'Spreadsheet',
            'helper' => 'Een .xlsx- of .csv-bestand. De eerste niet-lege rij moet de kolomkoppen bevatten.',
        ],
        'mapping' => [
            'helper' => 'Kies welke kolom uit je bestand elk veld vult. Koppelingen zijn automatisch voorgesteld; laat een veld leeg om het over te slaan.',
            'placeholder' => 'Niet importeren',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importeren voltooid',
            'body' => ':created aangemaakt, :updated bijgewerkt, :failed mislukt.',
            'body_with_skipped' => ':created aangemaakt, :updated bijgewerkt, :skipped overgeslagen, :failed mislukt.',
        ],
        'failed' => [
            'title' => 'Importeren kon niet starten',
        ],
        'download_failures' => 'Mislukte rijen downloaden',
    ],

    'errors' => [
        'too_many_rows' => 'Dit bestand heeft meer dan :limit rijen, de limiet voor een directe import. Splits het bestand of verhoog filament-import.sync_row_limit.',
        'unexpected' => 'Onverwachte fout. De rij is niet geïmporteerd.',
        'unreadable' => 'Het bestand kon niet worden gelezen. Upload een .xlsx- of .csv-bestand met een koprij.',
        'no_headers' => 'Het bestand heeft geen koprij.',
        'legacy_xls' => 'Dit is een ouder .xls-bestand. Open het in Excel, sla het op als .xlsx en upload het opnieuw.',
        'too_large_uncompressed' => 'Uitgepakt bevat deze spreadsheet te veel gegevens om veilig te lezen. Splits hem op in kleinere bestanden.',
    ],

    'failures_file' => [
        'row_column' => 'Rij',
        'errors_column' => 'Fouten',
    ],

];
