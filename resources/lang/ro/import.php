<?php

return [

    'action' => [
        'label' => 'Importă',
        'modal_heading' => 'Importă :label',
        'submit' => 'Importă',
        'download_template' => 'Descarcă șablonul',
    ],

    'steps' => [
        'upload' => 'Încarcă fișierul',
        'mapping' => 'Asociază coloanele',
    ],

    'fields' => [
        'file' => [
            'label' => 'Foaie de calcul',
            'helper' => 'Un fișier .xlsx sau .csv. Primul său rând care nu este gol trebuie să conțină antetele coloanelor.',
        ],
        'mapping' => [
            'helper' => 'Alegeți ce coloană din fișier completează fiecare câmp. Asocierile au fost sugerate automat; lăsați un câmp gol pentru a-l omite.',
            'placeholder' => 'Nu importa',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import finalizat',
            'body' => 'Create: :created, actualizate: :updated, eșuate: :failed.',
            'body_with_skipped' => 'Create: :created, actualizate: :updated, omise: :skipped, eșuate: :failed.',
        ],
        'failed' => [
            'title' => 'Importul nu a putut începe',
        ],
        'download_failures' => 'Descarcă rândurile eșuate',
    ],

    'errors' => [
        'too_many_rows' => 'Acest fișier are mai mult de :limit rânduri, limita pentru un import imediat. Împărțiți fișierul sau măriți filament-import.sync_row_limit.',
        'unexpected' => 'Eroare neașteptată. Rândul nu a fost importat.',
        'unreadable' => 'Fișierul nu a putut fi citit. Încărcați un fișier .xlsx sau .csv cu un rând de antet.',
        'no_headers' => 'Fișierul nu are un rând de antet.',
        'legacy_xls' => 'Acesta este un fișier .xls de tip vechi. Deschideți-l în Excel, salvați-l ca .xlsx, apoi încărcați-l din nou.',
        'too_large_uncompressed' => 'Această foaie de calcul se extinde la prea multe date pentru a fi citită în siguranță. Împărțiți-o în fișiere mai mici.',
    ],

    'failures_file' => [
        'row_column' => 'Rând',
        'errors_column' => 'Erori',
    ],

];
