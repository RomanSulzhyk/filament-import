<?php

return [

    'action' => [
        'label' => 'ייבוא',
        'modal_heading' => 'ייבוא :label',
        'submit' => 'ייבוא',
        'download_template' => 'הורדת תבנית',
    ],

    'steps' => [
        'upload' => 'העלאת קובץ',
        'mapping' => 'התאמת עמודות',
    ],

    'fields' => [
        'file' => [
            'label' => 'גיליון אלקטרוני',
            'helper' => 'קובץ .xlsx או .csv. השורה הראשונה שאינה ריקה בו חייבת להכיל את כותרות העמודות.',
        ],
        'mapping' => [
            'helper' => 'בחרו איזו עמודה בקובץ שלכם תמלא כל שדה. ההתאמות הוצעו אוטומטית; השאירו שדה ריק כדי לדלג עליו.',
            'placeholder' => 'לא לייבא',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'הייבוא הושלם',
            'body' => 'נוצרו: :created, עודכנו: :updated, נכשלו: :failed.',
            'body_with_skipped' => 'נוצרו: :created, עודכנו: :updated, דולגו: :skipped, נכשלו: :failed.',
        ],
        'failed' => [
            'title' => 'לא ניתן היה להתחיל את הייבוא',
        ],
        'download_failures' => 'הורדת השורות שנכשלו',
    ],

    'errors' => [
        'too_many_rows' => 'בקובץ זה יש יותר מ-:limit שורות, וזו המגבלה לייבוא מיידי. פצלו את הקובץ או הגדילו את filament-import.sync_row_limit.',
        'unexpected' => 'שגיאה בלתי צפויה. השורה לא יובאה.',
        'unreadable' => 'לא ניתן היה לקרוא את הקובץ. העלו קובץ .xlsx או .csv עם שורת כותרות.',
        'no_headers' => 'בקובץ אין שורת כותרות.',
        'legacy_xls' => 'זהו קובץ .xls בפורמט ישן. פתחו אותו ב-Excel, שמרו אותו כ-.xlsx והעלו אותו שוב.',
        'too_large_uncompressed' => 'גיליון זה נפרס לכמות נתונים גדולה מכדי לקרוא אותה בבטחה. פצלו אותו לקבצים קטנים יותר.',
    ],

    'failures_file' => [
        'row_column' => 'שורה',
        'errors_column' => 'שגיאות',
    ],

];
