<?php

return [

    'action' => [
        'label' => 'Importar',
        'modal_heading' => 'Importar :label',
        'submit' => 'Importar',
        'download_template' => 'Baixa la plantilla',
    ],

    'steps' => [
        'upload' => 'Pujar arxiu',
        'mapping' => 'Assignar columnes',
    ],

    'fields' => [
        'file' => [
            'label' => 'Full de càlcul',
            'helper' => 'Un arxiu .xlsx o .csv. La primera fila no buida ha de contenir les capçaleres de les columnes.',
        ],
        'mapping' => [
            'helper' => 'Trieu quina columna del vostre arxiu omple cada camp. Les coincidències s\'han suggerit automàticament; deixeu un camp buit per ometre\'l.',
            'placeholder' => 'No importar',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importació completada',
            'body' => ':created creats, :updated actualitzats, :failed fallits.',
            'body_with_skipped' => ':created creats, :updated actualitzats, :skipped omesos, :failed fallits.',
        ],
        'failed' => [
            'title' => 'No s\'ha pogut iniciar la importació',
        ],
        'download_failures' => 'Descarregar les files fallides',
    ],

    'errors' => [
        'too_many_rows' => 'Aquest arxiu té més de :limit files, que és el límit per a una importació immediata. Dividiu l\'arxiu o augmenteu filament-import.sync_row_limit.',
        'unexpected' => 'Error inesperat. La fila no s\'ha importat.',
        'unreadable' => 'No s\'ha pogut llegir l\'arxiu. Pugeu un arxiu .xlsx o .csv amb una fila de capçalera.',
        'no_headers' => 'L\'arxiu no té fila de capçalera.',
        'legacy_xls' => 'Aquest és un arxiu .xls de format antic. Obriu-lo a Excel, deseu-lo com a .xlsx i torneu-lo a pujar.',
        'too_large_uncompressed' => 'Un cop descomprimit, aquest full de càlcul conté massa dades per llegir-lo de manera segura. Dividiu-lo en arxius més petits.',
    ],

    'failures_file' => [
        'row_column' => 'Fila',
        'errors_column' => 'Errors',
    ],

];
