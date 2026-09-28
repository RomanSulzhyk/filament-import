<?php

return [

    'action' => [
        'label' => 'Importuj',
        'modal_heading' => 'Importuj :label',
        'submit' => 'Importuj',
        'download_template' => 'Pobierz szablon',
    ],

    'steps' => [
        'upload' => 'Prześlij plik',
        'mapping' => 'Dopasuj kolumny',
    ],

    'fields' => [
        'file' => [
            'label' => 'Arkusz kalkulacyjny',
            'helper' => 'Plik .xlsx lub .csv. Jego pierwszy niepusty wiersz musi zawierać nagłówki kolumn.',
        ],
        'mapping' => [
            'helper' => 'Wybierz, która kolumna pliku wypełnia każde pole. Dopasowania zostały zaproponowane automatycznie; pozostaw pole puste, aby je pominąć.',
            'placeholder' => 'Nie importuj',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import zakończony',
            'body' => 'Utworzono: :created, zaktualizowano: :updated, błędy: :failed.',
            'body_with_skipped' => 'Utworzono: :created, zaktualizowano: :updated, pominięto: :skipped, błędy: :failed.',
        ],
        'failed' => [
            'title' => 'Nie udało się rozpocząć importu',
        ],
        'download_failures' => 'Pobierz wiersze z błędami',
    ],

    'errors' => [
        'too_many_rows' => 'Ten plik ma więcej niż :limit wierszy, co jest limitem dla natychmiastowego importu. Podziel plik lub zwiększ filament-import.sync_row_limit.',
        'unexpected' => 'Nieoczekiwany błąd. Wiersz nie został zaimportowany.',
        'unreadable' => 'Nie udało się odczytać pliku. Prześlij plik .xlsx lub .csv z wierszem nagłówków.',
        'no_headers' => 'Plik nie ma wiersza nagłówków.',
        'legacy_xls' => 'To jest plik .xls w starym formacie. Otwórz go w Excel, zapisz jako .xlsx i prześlij ponownie.',
        'too_large_uncompressed' => 'Po rozpakowaniu ten arkusz zawiera zbyt dużo danych, aby bezpiecznie go odczytać. Podziel go na mniejsze pliki.',
    ],

    'failures_file' => [
        'row_column' => 'Wiersz',
        'errors_column' => 'Błędy',
    ],

];
