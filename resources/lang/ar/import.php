<?php

return [

    'action' => [
        'label' => 'استيراد',
        'modal_heading' => 'استيراد :label',
        'submit' => 'استيراد',
        'download_template' => 'تنزيل القالب',
    ],

    'steps' => [
        'upload' => 'رفع الملف',
        'mapping' => 'مطابقة الأعمدة',
    ],

    'fields' => [
        'file' => [
            'label' => 'جدول البيانات',
            'helper' => 'ملف .xlsx أو .csv. يجب أن يحتوي أول صف غير فارغ فيه على عناوين الأعمدة.',
        ],
        'mapping' => [
            'helper' => 'اختر عمود ملفك الذي يملأ كل حقل. اقتُرحت المطابقات تلقائيًا؛ اترك الحقل فارغًا لتخطيه.',
            'placeholder' => 'عدم الاستيراد',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'اكتمل الاستيراد',
            'body' => 'أُنشئ: :created، حُدّث: :updated، فشل: :failed.',
            'body_with_skipped' => 'أُنشئ: :created، حُدّث: :updated، تُخطّي: :skipped، فشل: :failed.',
        ],
        'failed' => [
            'title' => 'تعذّر بدء الاستيراد',
        ],
        'download_failures' => 'تنزيل الصفوف الفاشلة',
    ],

    'errors' => [
        'too_many_rows' => 'يحتوي هذا الملف على أكثر من :limit صف، وهو الحد الأقصى للاستيراد الفوري. قسّم الملف أو ارفع قيمة filament-import.sync_row_limit.',
        'unexpected' => 'خطأ غير متوقع. لم يُستورد الصف.',
        'unreadable' => 'تعذّرت قراءة الملف. ارفع ملف .xlsx أو .csv يحتوي على صف عناوين.',
        'no_headers' => 'لا يحتوي الملف على صف عناوين.',
        'legacy_xls' => 'هذا ملف .xls بتنسيق قديم. افتحه في Excel واحفظه بصيغة .xlsx، ثم ارفعه مرة أخرى.',
        'too_large_uncompressed' => 'عند فك جدول البيانات هذا تنتج بيانات أكبر من أن تُقرأ بأمان. قسّمه إلى ملفات أصغر.',
    ],

    'failures_file' => [
        'row_column' => 'الصف',
        'errors_column' => 'الأخطاء',
    ],

];
