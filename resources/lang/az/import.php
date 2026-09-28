<?php

return [

    'action' => [
        'label' => 'İdxal',
        'modal_heading' => 'İdxal: :label',
        'submit' => 'İdxal et',
        'download_template' => 'Şablonu yüklə',
    ],

    'steps' => [
        'upload' => 'Fayl yükləyin',
        'mapping' => 'Sütunları uyğunlaşdırın',
    ],

    'fields' => [
        'file' => [
            'label' => 'Cədvəl',
            'helper' => 'Bir .xlsx və ya .csv faylı. Onun boş olmayan ilk sətri sütun başlıqlarını ehtiva etməlidir.',
        ],
        'mapping' => [
            'helper' => 'Faylınızın hansı sütununun hər sahəni dolduracağını seçin. Uyğunluqlar avtomatik təklif olunub; sahəni ötürmək üçün onu boş saxlayın.',
            'placeholder' => 'İdxal etmə',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'İdxal tamamlandı',
            'body' => 'Yaradıldı: :created, yeniləndi: :updated, uğursuz: :failed.',
            'body_with_skipped' => 'Yaradıldı: :created, yeniləndi: :updated, ötürüldü: :skipped, uğursuz: :failed.',
        ],
        'failed' => [
            'title' => 'İdxala başlamaq mümkün olmadı',
        ],
        'download_failures' => 'Uğursuz sətirləri endir',
    ],

    'errors' => [
        'too_many_rows' => 'Bu faylda ani idxal üçün limit olan :limit sətirdən çox sətir var. Faylı bölün və ya filament-import.sync_row_limit dəyərini artırın.',
        'unexpected' => 'Gözlənilməz xəta. Sətir idxal edilmədi.',
        'unreadable' => 'Faylı oxumaq mümkün olmadı. Başlıq sətri olan .xlsx və ya .csv faylı yükləyin.',
        'no_headers' => 'Faylda başlıq sətri yoxdur.',
        'legacy_xls' => 'Bu köhnə formatlı .xls faylıdır. Onu Excel-də açın, .xlsx kimi saxlayın və yenidən yükləyin.',
        'too_large_uncompressed' => 'Bu cədvəl açıldıqda təhlükəsiz oxunmaq üçün həddindən artıq çox məlumata çevrilir. Onu daha kiçik fayllara bölün.',
    ],

    'failures_file' => [
        'row_column' => 'Sətir',
        'errors_column' => 'Xətalar',
    ],

];
