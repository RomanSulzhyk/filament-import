<?php

return [

    'action' => [
        'label' => 'Importer',
        'modal_heading' => 'Importer :label',
        'submit' => 'Importer',
        'download_template' => 'Télécharger le modèle',
    ],

    'steps' => [
        'upload' => 'Téléverser le fichier',
        'mapping' => 'Associer les colonnes',
    ],

    'fields' => [
        'file' => [
            'label' => 'Feuille de calcul',
            'helper' => 'Un fichier .xlsx ou .csv. Sa première ligne non vide doit contenir les en-têtes de colonnes.',
        ],
        'mapping' => [
            'helper' => 'Choisissez quelle colonne de votre fichier alimente chaque champ. Des correspondances ont été proposées automatiquement ; laissez un champ vide pour l\'ignorer.',
            'placeholder' => 'Ne pas importer',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Importation terminée',
            'body' => ':created créé(s), :updated mis à jour, :failed en échec.',
            'body_with_skipped' => ':created créé(s), :updated mis à jour, :skipped ignoré(s), :failed en échec.',
        ],
        'failed' => [
            'title' => 'L\'importation n\'a pas pu démarrer',
        ],
        'download_failures' => 'Télécharger les lignes en échec',
    ],

    'errors' => [
        'too_many_rows' => 'Ce fichier contient plus de :limit lignes, la limite pour une importation immédiate. Divisez le fichier ou augmentez filament-import.sync_row_limit.',
        'unexpected' => 'Erreur inattendue. La ligne n\'a pas été importée.',
        'unreadable' => 'Le fichier n\'a pas pu être lu. Téléversez un fichier .xlsx ou .csv avec une ligne d\'en-tête.',
        'no_headers' => 'Le fichier n\'a pas de ligne d\'en-tête.',
        'legacy_xls' => 'Il s\'agit d\'un ancien fichier .xls. Ouvrez-le dans Excel, enregistrez-le au format .xlsx, puis téléversez-le à nouveau.',
        'too_large_uncompressed' => 'Une fois décompressée, cette feuille de calcul contient trop de données pour être lue en toute sécurité. Divisez-la en fichiers plus petits.',
    ],

    'failures_file' => [
        'row_column' => 'Ligne',
        'errors_column' => 'Erreurs',
    ],

];
