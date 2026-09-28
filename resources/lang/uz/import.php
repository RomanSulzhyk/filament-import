<?php

return [

    'action' => [
        'label' => 'Import',
        'modal_heading' => ':labelni import qilish',
        'submit' => 'Import qilish',
        'download_template' => 'Shablonni yuklab olish',
    ],

    'steps' => [
        'upload' => 'Faylni yuklang',
        'mapping' => 'Ustunlarni moslashtirish',
    ],

    'fields' => [
        'file' => [
            'label' => 'Jadval',
            'helper' => 'Bir .xlsx yoki .csv fayl. Uning birinchi bo\'sh bo\'lmagan qatorida ustun sarlavhalari bo\'lishi kerak.',
        ],
        'mapping' => [
            'helper' => 'Faylingizning qaysi ustuni har bir maydonni to\'ldirishini tanlang. Mosliklar avtomatik taklif qilindi; maydonni o\'tkazib yuborish uchun uni bo\'sh qoldiring.',
            'placeholder' => 'Import qilinmasin',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Import yakunlandi',
            'body' => 'Yaratildi: :created, yangilandi: :updated, muvaffaqiyatsiz: :failed.',
            'body_with_skipped' => 'Yaratildi: :created, yangilandi: :updated, o\'tkazib yuborildi: :skipped, muvaffaqiyatsiz: :failed.',
        ],
        'failed' => [
            'title' => 'Importni boshlab bo\'lmadi',
        ],
        'download_failures' => 'Muvaffaqiyatsiz qatorlarni yuklab olish',
    ],

    'errors' => [
        'too_many_rows' => 'Bu faylda :limit tadan ortiq qator bor, bu esa darhol import qilish chegarasi. Faylni bo\'ling yoki filament-import.sync_row_limit qiymatini oshiring.',
        'unexpected' => 'Kutilmagan xato. Qator import qilinmadi.',
        'unreadable' => 'Faylni o\'qib bo\'lmadi. Sarlavha qatori bor .xlsx yoki .csv faylni yuklang.',
        'no_headers' => 'Faylda sarlavha qatori yo\'q.',
        'legacy_xls' => 'Bu eski formatdagi .xls fayl. Uni Excel\'da oching, .xlsx sifatida saqlang va qayta yuklang.',
        'too_large_uncompressed' => 'Bu jadval ochilganda xavfsiz o\'qib bo\'lmaydigan darajada ko\'p ma\'lumotga aylanadi. Uni kichikroq fayllarga bo\'ling.',
    ],

    'failures_file' => [
        'row_column' => 'Qator',
        'errors_column' => 'Xatolar',
    ],

];
