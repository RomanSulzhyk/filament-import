<?php

return [

    'action' => [
        'label' => 'Importieren',
        'modal_heading' => ':label importieren',
        'submit' => 'Importieren',
        'download_template' => 'Vorlage herunterladen',
    ],

    'steps' => [
        'upload' => 'Datei hochladen',
        'mapping' => 'Spalten zuordnen',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tabelle',
            'helper' => 'Eine .xlsx- oder .csv-Datei. Die erste nicht leere Zeile muss die Spaltenüberschriften enthalten.',
        ],
        'mapping' => [
            'helper' => 'Wählen Sie, welche Spalte Ihrer Datei jedes Feld befüllt. Zuordnungen wurden automatisch vorgeschlagen; lassen Sie ein Feld leer, um es zu überspringen.',
            'placeholder' => 'Nicht importieren',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import abgeschlossen',
            'body' => ':created erstellt, :updated aktualisiert, :failed fehlgeschlagen.',
            'body_with_skipped' => ':created erstellt, :updated aktualisiert, :skipped übersprungen, :failed fehlgeschlagen.',
        ],
        'failed' => [
            'title' => 'Import konnte nicht gestartet werden',
        ],
        'download_failures' => 'Fehlgeschlagene Zeilen herunterladen',
    ],

    'errors' => [
        'too_many_rows' => 'Diese Datei hat mehr als :limit Zeilen, das Limit für einen sofortigen Import. Teilen Sie die Datei auf oder erhöhen Sie filament-import.sync_row_limit.',
        'unexpected' => 'Unerwarteter Fehler. Die Zeile wurde nicht importiert.',
        'unreadable' => 'Die Datei konnte nicht gelesen werden. Laden Sie eine .xlsx- oder .csv-Datei mit einer Kopfzeile hoch.',
        'no_headers' => 'Die Datei hat keine Kopfzeile.',
        'legacy_xls' => 'Dies ist eine .xls-Datei im alten Format. Öffnen Sie sie in Excel, speichern Sie sie als .xlsx und laden Sie sie erneut hoch.',
        'too_large_uncompressed' => 'Diese Tabelle entpackt sich zu einer Datenmenge, die nicht sicher gelesen werden kann. Teilen Sie sie in kleinere Dateien auf.',
    ],

    'failures_file' => [
        'row_column' => 'Zeile',
        'errors_column' => 'Fehler',
    ],

];
