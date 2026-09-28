<?php

return [

    'action' => [
        'label' => 'ኢምፖርት',
        'modal_heading' => ':labelን ኢምፖርት',
        'submit' => 'ኢምፖርት',
        'download_template' => 'አብነት አውርድ',
    ],

    'steps' => [
        'upload' => 'ፋይል ይስቀሉ',
        'mapping' => 'አምዶችን ያዛምዱ',
    ],

    'fields' => [
        'file' => [
            'label' => 'የተመን ሉህ',
            'helper' => '.xlsx ወይም .csv ፋይል። የመጀመሪያው ባዶ ያልሆነ ረድፉ የአምድ ርዕሶችን መያዝ አለበት።',
        ],
        'mapping' => [
            'helper' => 'የፋይልዎ የትኛው አምድ እያንዳንዱን መስክ እንደሚሞላ ይምረጡ። ተዛማጆች በራስ-ሰር ተጠቁመዋል፤ መስክን ለመዝለል ባዶ ይተዉት።',
            'placeholder' => 'ኢምፖርት አታድርግ',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'ኢምፖርቱ ተጠናቋል',
            'body' => 'የተፈጠሩ፦ :created፣ የተዘመኑ፦ :updated፣ ያልተሳኩ፦ :failed።',
            'body_with_skipped' => 'የተፈጠሩ፦ :created፣ የተዘመኑ፦ :updated፣ የተዘለሉ፦ :skipped፣ ያልተሳኩ፦ :failed።',
        ],
        'failed' => [
            'title' => 'ኢምፖርቱን መጀመር አልተቻለም',
        ],
        'download_failures' => 'ያልተሳኩ ረድፎችን ያውርዱ',
    ],

    'errors' => [
        'too_many_rows' => 'ይህ ፋይል ከ:limit በላይ ረድፎች አሉት፤ ይህም ለፈጣን ኢምፖርት ገደቡ ነው። ፋይሉን ይከፋፍሉ ወይም filament-import.sync_row_limit ይጨምሩ።',
        'unexpected' => 'ያልተጠበቀ ስህተት። ረድፉ ኢምፖርት አልተደረገም።',
        'unreadable' => 'ፋይሉ ሊነበብ አልቻለም። የርዕስ ረድፍ ያለው .xlsx ወይም .csv ፋይል ይስቀሉ።',
        'no_headers' => 'ፋይሉ የርዕስ ረድፍ የለውም።',
        'legacy_xls' => 'ይህ የድሮ ቅርጸት .xls ፋይል ነው። በExcel ይክፈቱት፣ እንደ .xlsx ያስቀምጡት፣ ከዚያ እንደገና ይስቀሉት።',
        'too_large_uncompressed' => 'ይህ የተመን ሉህ ሲከፈት በደህንነት ለማንበብ በጣም ብዙ ውሂብ ይሆናል። ወደ ትናንሽ ፋይሎች ይከፋፍሉት።',
    ],

    'failures_file' => [
        'row_column' => 'ረድፍ',
        'errors_column' => 'ስህተቶች',
    ],

];
