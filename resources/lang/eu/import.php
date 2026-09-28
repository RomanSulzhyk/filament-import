<?php

return [

    'action' => [
        'label' => 'Inportatu',
        'modal_heading' => 'Inportatu :label',
        'submit' => 'Inportatu',
        'download_template' => 'Deskargatu txantiloia',
    ],

    'steps' => [
        'upload' => 'Igo fitxategia',
        'mapping' => 'Lotu zutabeak',
    ],

    'fields' => [
        'file' => [
            'label' => 'Kalkulu-orria',
            'helper' => '.xlsx edo .csv fitxategi bat. Hutsik ez dagoen lehen errenkadak zutabeen goiburuak izan behar ditu.',
        ],
        'mapping' => [
            'helper' => 'Aukeratu zure fitxategiko zein zutabek betetzen duen eremu bakoitza. Bat-etortzeak automatikoki iradoki dira; utzi eremu bat hutsik saltatzeko.',
            'placeholder' => 'Ez inportatu',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Inportazioa amaituta',
            'body' => ':created sortuta, :updated eguneratuta, :failed huts eginda.',
            'body_with_skipped' => ':created sortuta, :updated eguneratuta, :skipped saltatuta, :failed huts eginda.',
        ],
        'failed' => [
            'title' => 'Ezin izan da inportazioa hasi',
        ],
        'download_failures' => 'Deskargatu huts egindako errenkadak',
    ],

    'errors' => [
        'too_many_rows' => 'Fitxategi honek :limit errenkada baino gehiago ditu, eta hori da berehalako inportazio baten muga. Zatitu fitxategia edo handitu filament-import.sync_row_limit.',
        'unexpected' => 'Ustekabeko errorea. Errenkada ez da inportatu.',
        'unreadable' => 'Ezin izan da fitxategia irakurri. Igo goiburu-errenkada duen .xlsx edo .csv fitxategi bat.',
        'no_headers' => 'Fitxategiak ez du goiburu-errenkadarik.',
        'legacy_xls' => 'Formatu zaharreko .xls fitxategia da hau. Ireki Excel-en, gorde .xlsx gisa eta igo berriro.',
        'too_large_uncompressed' => 'Deskonprimitu ondoren, kalkulu-orri honek datu gehiegi ditu segurtasunez irakurtzeko. Zatitu fitxategi txikiagotan.',
    ],

    'failures_file' => [
        'row_column' => 'Errenkada',
        'errors_column' => 'Erroreak',
    ],

];
