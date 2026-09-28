<?php

return [

    'action' => [
        'label' => '匯入',
        'modal_heading' => '匯入:label',
        'submit' => '匯入',
        'download_template' => '下載範本',
    ],

    'steps' => [
        'upload' => '上傳檔案',
        'mapping' => '對應欄位',
    ],

    'fields' => [
        'file' => [
            'label' => '試算表',
            'helper' => '.xlsx 或 .csv 檔案。第一個非空白列必須包含欄位標題。',
        ],
        'mapping' => [
            'helper' => '選擇檔案中的哪一欄對應每個欄位。系統已自動建議對應；留空即可略過該欄位。',
            'placeholder' => '不匯入',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => '匯入完成',
            'body' => '已建立 :created 筆，已更新 :updated 筆，失敗 :failed 筆。',
            'body_with_skipped' => '已建立 :created 筆，已更新 :updated 筆，已略過 :skipped 筆，失敗 :failed 筆。',
        ],
        'failed' => [
            'title' => '無法開始匯入',
        ],
        'download_failures' => '下載匯入失敗的列',
    ],

    'errors' => [
        'too_many_rows' => '此檔案超過 :limit 列，這是立即匯入的上限。請分割檔案，或調高 filament-import.sync_row_limit。',
        'unexpected' => '發生未預期的錯誤，該列未匯入。',
        'unreadable' => '無法讀取檔案。請上傳含標題列的 .xlsx 或 .csv 檔案。',
        'no_headers' => '檔案沒有標題列。',
        'legacy_xls' => '這是舊格式的 .xls 檔案。請用 Excel 開啟並另存為 .xlsx，然後重新上傳。',
        'too_large_uncompressed' => '此試算表解壓後的資料量過大，無法安全讀取。請將其分割成較小的檔案。',
    ],

    'failures_file' => [
        'row_column' => '列',
        'errors_column' => '錯誤',
    ],

];
