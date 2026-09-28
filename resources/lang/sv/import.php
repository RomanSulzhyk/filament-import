<?php

return [

    'action' => [
        'label' => 'Importera',
        'modal_heading' => 'Importera :label',
        'submit' => 'Importera',
        'download_template' => 'Ladda ner mall',
    ],

    'steps' => [
        'upload' => 'Ladda upp fil',
        'mapping' => 'Koppla kolumner',
    ],

    'fields' => [
        'file' => [
            'label' => 'Kalkylblad',
            'helper' => 'En .xlsx- eller .csv-fil. Den första icke-tomma raden måste innehålla kolumnrubrikerna.',
        ],
        'mapping' => [
            'helper' => 'Välj vilken kolumn i din fil som fyller varje fält. Kopplingar har föreslagits automatiskt; lämna ett fält tomt för att hoppa över det.',
            'placeholder' => 'Importera inte',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import slutförd',
            'body' => ':created skapade, :updated uppdaterade, :failed misslyckade.',
            'body_with_skipped' => ':created skapade, :updated uppdaterade, :skipped överhoppade, :failed misslyckade.',
        ],
        'failed' => [
            'title' => 'Importen kunde inte starta',
        ],
        'download_failures' => 'Ladda ner misslyckade rader',
    ],

    'errors' => [
        'too_many_rows' => 'Filen har fler än :limit rader, vilket är gränsen för en direkt import. Dela upp filen eller höj filament-import.sync_row_limit.',
        'unexpected' => 'Oväntat fel. Raden importerades inte.',
        'unreadable' => 'Filen kunde inte läsas. Ladda upp en .xlsx- eller .csv-fil med en rubrikrad.',
        'no_headers' => 'Filen saknar rubrikrad.',
        'legacy_xls' => 'Det här är en .xls-fil i det gamla formatet. Öppna den i Excel, spara den som .xlsx och ladda upp den igen.',
        'too_large_uncompressed' => 'Uppackat innehåller kalkylbladet för mycket data för att läsas säkert. Dela upp det i mindre filer.',
    ],

    'failures_file' => [
        'row_column' => 'Rad',
        'errors_column' => 'Fel',
    ],

];
