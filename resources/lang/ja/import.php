<?php

return [

    'action' => [
        'label' => 'インポート',
        'modal_heading' => ':labelをインポート',
        'submit' => 'インポート',
        'download_template' => 'テンプレートをダウンロード',
    ],

    'steps' => [
        'upload' => 'ファイルをアップロード',
        'mapping' => '列の対応付け',
    ],

    'fields' => [
        'file' => [
            'label' => 'スプレッドシート',
            'helper' => '.xlsx または .csv ファイル。最初の空でない行に列見出しが必要です。',
        ],
        'mapping' => [
            'helper' => 'ファイルのどの列を各フィールドに使うか選択してください。候補は自動で提案されています。フィールドを空欄にするとスキップされます。',
            'placeholder' => 'インポートしない',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'インポート完了',
            'body' => '作成 :created 件、更新 :updated 件、失敗 :failed 件。',
            'body_with_skipped' => '作成 :created 件、更新 :updated 件、スキップ :skipped 件、失敗 :failed 件。',
        ],
        'failed' => [
            'title' => 'インポートを開始できませんでした',
        ],
        'download_failures' => '失敗した行をダウンロード',
    ],

    'errors' => [
        'too_many_rows' => 'このファイルは即時インポートの上限である :limit 行を超えています。ファイルを分割するか、filament-import.sync_row_limit を引き上げてください。',
        'unexpected' => '予期しないエラーです。この行はインポートされませんでした。',
        'unreadable' => 'ファイルを読み取れませんでした。見出し行のある .xlsx または .csv ファイルをアップロードしてください。',
        'no_headers' => 'ファイルに見出し行がありません。',
        'legacy_xls' => 'これは旧形式の .xls ファイルです。Excel で開いて .xlsx として保存し、もう一度アップロードしてください。',
        'too_large_uncompressed' => 'このスプレッドシートは展開後のデータ量が大きすぎるため、安全に読み取れません。小さなファイルに分割してください。',
    ],

    'failures_file' => [
        'row_column' => '行',
        'errors_column' => 'エラー',
    ],

];
