<?php

return [

    'action' => [
        'label' => 'Ingiza',
        'modal_heading' => 'Ingiza :label',
        'submit' => 'Ingiza',
        'download_template' => 'Pakua kiolezo',
    ],

    'steps' => [
        'upload' => 'Pakia faili',
        'mapping' => 'Linganisha safu wima',
    ],

    'fields' => [
        'file' => [
            'label' => 'Lahajedwali',
            'helper' => 'Faili ya .xlsx au .csv. Mstari wake wa kwanza usio tupu lazima uwe na vichwa vya safu wima.',
        ],
        'mapping' => [
            'helper' => 'Chagua ni safu wima ipi ya faili yako inajaza kila sehemu. Ulinganisho umependekezwa kiotomatiki; acha sehemu tupu ili kuiruka.',
            'placeholder' => 'Usiingize',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Kuingiza Kumekamilika',
            'body' => 'Zimeundwa: :created, zimesasishwa: :updated, zimeshindikana: :failed.',
            'body_with_skipped' => 'Zimeundwa: :created, zimesasishwa: :updated, zimerukwa: :skipped, zimeshindikana: :failed.',
        ],
        'failed' => [
            'title' => 'Kuingiza hakukuweza kuanza',
        ],
        'download_failures' => 'Pakua mistari iliyoshindikana',
    ],

    'errors' => [
        'too_many_rows' => 'Faili hili lina mistari zaidi ya :limit, ambacho ndicho kikomo cha kuingiza papo hapo. Gawanya faili, au ongeza filament-import.sync_row_limit.',
        'unexpected' => 'Hitilafu isiyotarajiwa. Mstari haukuingizwa.',
        'unreadable' => 'Faili halikuweza kusomwa. Pakia faili ya .xlsx au .csv yenye mstari wa vichwa.',
        'no_headers' => 'Faili halina mstari wa vichwa.',
        'legacy_xls' => 'Hii ni faili ya .xls ya muundo wa zamani. Ifungue katika Excel, ihifadhi kama .xlsx, kisha uipakie tena.',
        'too_large_uncompressed' => 'Lahajedwali hili linapanuka kuwa data nyingi mno kusomwa kwa usalama. Ligawanye katika faili ndogo zaidi.',
    ],

    'failures_file' => [
        'row_column' => 'Mstari',
        'errors_column' => 'Hitilafu',
    ],

];
