<?php

return [

    'action' => [
        'label' => 'Importa',
        'modal_heading' => 'Importa :label',
        'submit' => 'Importa',
        'download_template' => 'Scarica il modello',
    ],

    'steps' => [
        'upload' => 'Carica file',
        'mapping' => 'Abbina le colonne',
    ],

    'fields' => [
        'file' => [
            'label' => 'Foglio di calcolo',
            'helper' => 'Un file .xlsx o .csv. La prima riga non vuota deve contenere le intestazioni delle colonne.',
        ],
        'mapping' => [
            'helper' => 'Scegli quale colonna del file alimenta ciascun campo. Le corrispondenze sono state suggerite automaticamente; lascia vuoto un campo per saltarlo.',
            'placeholder' => 'Non importare',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importazione completata',
            'body' => ':created creati, :updated aggiornati, :failed non riusciti.',
            'body_with_skipped' => ':created creati, :updated aggiornati, :skipped saltati, :failed non riusciti.',
        ],
        'failed' => [
            'title' => 'Impossibile avviare l\'importazione',
        ],
        'download_failures' => 'Scarica le righe non riuscite',
    ],

    'errors' => [
        'too_many_rows' => 'Questo file ha più di :limit righe, il limite per un\'importazione immediata. Dividi il file oppure aumenta filament-import.sync_row_limit.',
        'unexpected' => 'Errore imprevisto. La riga non è stata importata.',
        'unreadable' => 'Impossibile leggere il file. Carica un file .xlsx o .csv con una riga di intestazione.',
        'no_headers' => 'Il file non ha una riga di intestazione.',
        'legacy_xls' => 'Questo è un file .xls in formato obsoleto. Aprilo in Excel, salvalo come .xlsx e caricalo di nuovo.',
        'too_large_uncompressed' => 'Una volta decompresso, questo foglio di calcolo contiene troppi dati per essere letto in sicurezza. Dividilo in file più piccoli.',
    ],

    'failures_file' => [
        'row_column' => 'Riga',
        'errors_column' => 'Errori',
    ],

];
