<?php

return [

    'action' => [
        'label' => 'Importēt',
        'modal_heading' => 'Importēt :label',
        'submit' => 'Importēt',
        'download_template' => 'Lejupielādēt veidni',
    ],

    'steps' => [
        'upload' => 'Augšupielādēt failu',
        'mapping' => 'Saskaņot kolonnas',
    ],

    'fields' => [
        'file' => [
            'label' => 'Izklājlapa',
            'helper' => '.xlsx vai .csv fails. Tā pirmajā netukšajā rindā jābūt kolonnu virsrakstiem.',
        ],
        'mapping' => [
            'helper' => 'Izvēlieties, kura faila kolonna aizpilda katru lauku. Atbilstības tika ieteiktas automātiski; atstājiet lauku tukšu, lai to izlaistu.',
            'placeholder' => 'Neimportēt',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importēšana pabeigta',
            'body' => 'Izveidoti: :created, atjaunināti: :updated, neizdevās: :failed.',
            'body_with_skipped' => 'Izveidoti: :created, atjaunināti: :updated, izlaisti: :skipped, neizdevās: :failed.',
        ],
        'failed' => [
            'title' => 'Importēšanu nevarēja sākt',
        ],
        'download_failures' => 'Lejupielādēt neizdevušās rindas',
    ],

    'errors' => [
        'too_many_rows' => 'Šajā failā ir vairāk nekā :limit rindu, kas ir tūlītējas importēšanas ierobežojums. Sadaliet failu vai palieliniet filament-import.sync_row_limit.',
        'unexpected' => 'Neparedzēta kļūda. Rinda netika importēta.',
        'unreadable' => 'Failu nevarēja nolasīt. Augšupielādējiet .xlsx vai .csv failu ar virsrakstu rindu.',
        'no_headers' => 'Failā nav virsrakstu rindas.',
        'legacy_xls' => 'Šis ir vecā formāta .xls fails. Atveriet to programmā Excel, saglabājiet kā .xlsx un augšupielādējiet vēlreiz.',
        'too_large_uncompressed' => 'Šī izklājlapa izpakojot aizņem pārāk daudz datu, lai to varētu droši nolasīt. Sadaliet to mazākos failos.',
    ],

    'failures_file' => [
        'row_column' => 'Rinda',
        'errors_column' => 'Kļūdas',
    ],

];
