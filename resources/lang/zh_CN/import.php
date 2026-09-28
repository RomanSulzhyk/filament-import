<?php

return [

    'action' => [
        'label' => '导入',
        'modal_heading' => '导入 :label',
        'submit' => '导入',
        'download_template' => '下载模板',
    ],

    'steps' => [
        'upload' => '上传文件',
        'mapping' => '匹配列',
    ],

    'fields' => [
        'file' => [
            'label' => '电子表格',
            'helper' => '.xlsx 或 .csv 文件。第一个非空行必须包含列标题。',
        ],
        'mapping' => [
            'helper' => '选择文件中的哪一列对应每个字段。已自动建议匹配；留空即可跳过该字段。',
            'placeholder' => '不导入',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => '导入完成',
            'body' => '已创建 :created 条，已更新 :updated 条，失败 :failed 条。',
            'body_with_skipped' => '已创建 :created 条，已更新 :updated 条，已跳过 :skipped 条，失败 :failed 条。',
        ],
        'failed' => [
            'title' => '无法开始导入',
        ],
        'download_failures' => '下载导入失败的行',
    ],

    'errors' => [
        'too_many_rows' => '此文件超过 :limit 行，这是即时导入的上限。请拆分文件，或调高 filament-import.sync_row_limit。',
        'unexpected' => '意外错误，该行未导入。',
        'unreadable' => '无法读取文件。请上传带有标题行的 .xlsx 或 .csv 文件。',
        'no_headers' => '文件没有标题行。',
        'legacy_xls' => '这是旧格式的 .xls 文件。请在 Excel 中打开并另存为 .xlsx，然后重新上传。',
        'too_large_uncompressed' => '此电子表格解压后的数据量过大，无法安全读取。请将其拆分为较小的文件。',
    ],

    'failures_file' => [
        'row_column' => '行',
        'errors_column' => '错误',
    ],

];
