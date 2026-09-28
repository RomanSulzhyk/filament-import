<?php

return [

    'action' => [
        'label' => 'Importar',
        'modal_heading' => 'Importar :label',
        'submit' => 'Importar',
        'download_template' => 'Descargar plantilla',
    ],

    'steps' => [
        'upload' => 'Subir archivo',
        'mapping' => 'Asignar columnas',
    ],

    'fields' => [
        'file' => [
            'label' => 'Hoja de cálculo',
            'helper' => 'Un archivo .xlsx o .csv. Su primera fila no vacía debe contener los encabezados de las columnas.',
        ],
        'mapping' => [
            'helper' => 'Elija qué columna de su archivo alimenta cada campo. Las coincidencias se sugirieron automáticamente; deje un campo vacío para omitirlo.',
            'placeholder' => 'No importar',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importación completada',
            'body' => ':created creados, :updated actualizados, :failed fallidos.',
            'body_with_skipped' => ':created creados, :updated actualizados, :skipped omitidos, :failed fallidos.',
        ],
        'failed' => [
            'title' => 'No se pudo iniciar la importación',
        ],
        'download_failures' => 'Descargar filas fallidas',
    ],

    'errors' => [
        'too_many_rows' => 'Este archivo tiene más de :limit filas, que es el límite para una importación inmediata. Divida el archivo o aumente filament-import.sync_row_limit.',
        'unexpected' => 'Error inesperado. La fila no se importó.',
        'unreadable' => 'No se pudo leer el archivo. Suba un archivo .xlsx o .csv con una fila de encabezados.',
        'no_headers' => 'El archivo no tiene fila de encabezados.',
        'legacy_xls' => 'Este es un archivo .xls de formato antiguo. Ábralo en Excel, guárdelo como .xlsx y vuelva a subirlo.',
        'too_large_uncompressed' => 'Al descomprimirse, esta hoja de cálculo ocupa demasiados datos para leerla de forma segura. Divídala en archivos más pequeños.',
    ],

    'failures_file' => [
        'row_column' => 'Fila',
        'errors_column' => 'Errores',
    ],

];
