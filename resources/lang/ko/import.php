<?php

return [

    'action' => [
        'label' => '가져오기',
        'modal_heading' => ':label 가져오기',
        'submit' => '가져오기',
        'download_template' => '템플릿 다운로드',
    ],

    'steps' => [
        'upload' => '파일 업로드',
        'mapping' => '열 연결',
    ],

    'fields' => [
        'file' => [
            'label' => '스프레드시트',
            'helper' => '.xlsx 또는 .csv 파일입니다. 비어 있지 않은 첫 번째 행에 열 헤더가 있어야 합니다.',
        ],
        'mapping' => [
            'helper' => '각 필드에 사용할 파일의 열을 선택하세요. 일치 항목이 자동으로 제안되었습니다. 필드를 비워 두면 건너뜁니다.',
            'placeholder' => '가져오지 않음',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => '가져오기 완료',
            'body' => ':created개 생성, :updated개 수정, :failed개 실패.',
            'body_with_skipped' => ':created개 생성, :updated개 수정, :skipped개 건너뜀, :failed개 실패.',
        ],
        'failed' => [
            'title' => '가져오기를 시작할 수 없습니다',
        ],
        'download_failures' => '실패한 행 다운로드',
    ],

    'errors' => [
        'too_many_rows' => '이 파일은 즉시 가져오기 한도인 :limit개 행을 초과합니다. 파일을 나누거나 filament-import.sync_row_limit 값을 늘리세요.',
        'unexpected' => '예기치 않은 오류입니다. 이 행은 가져오지 못했습니다.',
        'unreadable' => '파일을 읽을 수 없습니다. 헤더 행이 있는 .xlsx 또는 .csv 파일을 업로드하세요.',
        'no_headers' => '파일에 헤더 행이 없습니다.',
        'legacy_xls' => '이전 형식의 .xls 파일입니다. Excel에서 열어 .xlsx로 저장한 뒤 다시 업로드하세요.',
        'too_large_uncompressed' => '이 스프레드시트는 압축을 풀면 데이터가 너무 커서 안전하게 읽을 수 없습니다. 더 작은 파일로 나누세요.',
    ],

    'failures_file' => [
        'row_column' => '행',
        'errors_column' => '오류',
    ],

];
