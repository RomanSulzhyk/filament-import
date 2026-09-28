<?php

return [

    'action' => [
        'label' => 'Nhập',
        'modal_heading' => 'Nhập :label',
        'submit' => 'Nhập',
        'download_template' => 'Tải xuống mẫu',
    ],

    'steps' => [
        'upload' => 'Tải lên tệp',
        'mapping' => 'Ghép cột',
    ],

    'fields' => [
        'file' => [
            'label' => 'Bảng tính',
            'helper' => 'Tệp .xlsx hoặc .csv. Hàng không trống đầu tiên phải chứa tiêu đề cột.',
        ],
        'mapping' => [
            'helper' => 'Chọn cột nào trong tệp của bạn sẽ dùng cho từng trường. Các cặp ghép đã được gợi ý tự động; để trống một trường để bỏ qua.',
            'placeholder' => 'Không nhập',
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Đã nhập xong',
            'body' => 'Đã tạo :created, đã cập nhật :updated, thất bại :failed.',
            'body_with_skipped' => 'Đã tạo :created, đã cập nhật :updated, bỏ qua :skipped, thất bại :failed.',
        ],
        'failed' => [
            'title' => 'Không thể bắt đầu nhập',
        ],
        'download_failures' => 'Tải xuống các hàng bị lỗi',
    ],

    'errors' => [
        'too_many_rows' => 'Tệp này có hơn :limit hàng, đây là giới hạn cho việc nhập ngay lập tức. Hãy chia nhỏ tệp hoặc tăng filament-import.sync_row_limit.',
        'unexpected' => 'Lỗi không mong muốn. Hàng này chưa được nhập.',
        'unreadable' => 'Không thể đọc tệp. Hãy tải lên tệp .xlsx hoặc .csv có hàng tiêu đề.',
        'no_headers' => 'Tệp không có hàng tiêu đề.',
        'legacy_xls' => 'Đây là tệp .xls định dạng cũ. Hãy mở bằng Excel, lưu dưới dạng .xlsx rồi tải lên lại.',
        'too_large_uncompressed' => 'Bảng tính này khi giải nén chứa quá nhiều dữ liệu để đọc an toàn. Hãy chia thành các tệp nhỏ hơn.',
    ],

    'failures_file' => [
        'row_column' => 'Hàng',
        'errors_column' => 'Lỗi',
    ],

];
