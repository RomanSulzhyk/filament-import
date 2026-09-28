<?php

return [

    'action' => [
        'label' => 'İçe Aktar',
        'modal_heading' => 'İçe Aktar: :label',
        'submit' => 'İçe Aktar',
        'download_template' => 'Şablonu indir',
    ],

    'steps' => [
        'upload' => 'Dosya yükle',
        'mapping' => 'Sütunları eşleştir',
    ],

    'fields' => [
        'file' => [
            'label' => 'Tablo dosyası',
            'helper' => 'Bir .xlsx veya .csv dosyası. Boş olmayan ilk satırı sütun başlıklarını içermelidir.',
        ],
        'mapping' => [
            'helper' => 'Dosyanızdaki hangi sütunun her alanı dolduracağını seçin. Eşleşmeler otomatik olarak önerildi; atlamak istediğiniz alanı boş bırakın.',
            'placeholder' => 'İçe aktarma',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'İçe Aktarım Tamamlandı',
            'body' => ':created oluşturuldu, :updated güncellendi, :failed başarısız.',
            'body_with_skipped' => ':created oluşturuldu, :updated güncellendi, :skipped atlandı, :failed başarısız.',
        ],
        'failed' => [
            'title' => 'İçe aktarım başlatılamadı',
        ],
        'download_failures' => 'Başarısız satırları indir',
    ],

    'errors' => [
        'too_many_rows' => 'Bu dosyada, anında içe aktarım sınırı olan :limit satırdan fazla satır var. Dosyayı bölün veya filament-import.sync_row_limit değerini artırın.',
        'unexpected' => 'Beklenmeyen hata. Satır içe aktarılmadı.',
        'unreadable' => 'Dosya okunamadı. Başlık satırı olan bir .xlsx veya .csv dosyası yükleyin.',
        'no_headers' => 'Dosyada başlık satırı yok.',
        'legacy_xls' => 'Bu eski biçimli bir .xls dosyası. Excel\'de açıp .xlsx olarak kaydedin, ardından tekrar yükleyin.',
        'too_large_uncompressed' => 'Bu tablo açıldığında güvenle okunamayacak kadar çok veriye dönüşüyor. Daha küçük dosyalara bölün.',
    ],

    'failures_file' => [
        'row_column' => 'Satır',
        'errors_column' => 'Hatalar',
    ],

];
