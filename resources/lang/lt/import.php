<?php

return [

    'action' => [
        'label' => 'Importuoti',
        'modal_heading' => 'Importuoti :label',
        'submit' => 'Importuoti',
        'download_template' => 'Atsisiųsti šabloną',
    ],

    'steps' => [
        'upload' => 'Įkelti failą',
        'mapping' => 'Susieti stulpelius',
    ],

    'fields' => [
        'file' => [
            'label' => 'Skaičiuoklė',
            'helper' => '.xlsx arba .csv failas. Pirmoje netuščioje jo eilutėje turi būti stulpelių antraštės.',
        ],
        'mapping' => [
            'helper' => 'Pasirinkite, kuris failo stulpelis užpildo kiekvieną lauką. Atitikmenys pasiūlyti automatiškai; palikite lauką tuščią, jei norite jį praleisti.',
            'placeholder' => 'Neimportuoti',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importas atliktas',
            'body' => 'Sukurta: :created, atnaujinta: :updated, nepavyko: :failed.',
            'body_with_skipped' => 'Sukurta: :created, atnaujinta: :updated, praleista: :skipped, nepavyko: :failed.',
        ],
        'failed' => [
            'title' => 'Nepavyko pradėti importo',
        ],
        'download_failures' => 'Atsisiųsti nepavykusias eilutes',
    ],

    'errors' => [
        'too_many_rows' => 'Šiame faile yra daugiau nei :limit eilučių, o tai yra momentinio importo riba. Padalykite failą arba padidinkite filament-import.sync_row_limit.',
        'unexpected' => 'Netikėta klaida. Eilutė neimportuota.',
        'unreadable' => 'Nepavyko nuskaityti failo. Įkelkite .xlsx arba .csv failą su antraščių eilute.',
        'no_headers' => 'Faile nėra antraščių eilutės.',
        'legacy_xls' => 'Tai senojo formato .xls failas. Atidarykite jį Excel, išsaugokite kaip .xlsx ir įkelkite dar kartą.',
        'too_large_uncompressed' => 'Išskleista ši skaičiuoklė užima per daug duomenų, kad ją būtų galima saugiai nuskaityti. Padalykite ją į mažesnius failus.',
    ],

    'failures_file' => [
        'row_column' => 'Eilutė',
        'errors_column' => 'Klaidos',
    ],

];
