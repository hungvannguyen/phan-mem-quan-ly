# Demo chức năng báo hỏng phôi văn bằng

## Tổng quan

Đã hoàn thành chức năng báo hỏng phôi văn bằng với dropdown lý do có sẵn từ DamageReason model.

## Các thành phần đã thêm:

### 1. Database Migration

- **File**: `database/migrations/2025_10_18_135243_add_damage_fields_to_diploma_blanks_table.php`
- **Chức năng**: Thêm 3 trường mới vào bảng `diploma_blanks`:
    - `damage_reason_id` (foreign key đến bảng `damage_reasons`)
    - `damage_description` (mô tả chi tiết tùy chọn)
    - `damage_date` (ngày báo hỏng)

### 2. Model Updates

- **DiplomaBlank Model**: Thêm relationship `damageReason()` và update fillable fields
- **DamageReason Model**: Sử dụng primary key mặc định `id`

### 3. Controller Enhancement

- **DiplomaBlankController**:
    - Thêm method `markAsDamaged()` để xử lý báo hỏng
    - Cập nhật `indexByImport()` để truyền dữ liệu `$damageReasons`
    - Validation cho damage_reason_id và damage_description

### 4. Route Addition

- **Route mới**: `POST /diploma-blanks/{diplomaBlankId}/mark-damaged`
- **Name**: `diploma-blanks.mark-damaged`

### 5. Frontend Implementation

- **Modal HTML**: Modal với dropdown lý do hỏng và textarea mô tả
- **JavaScript**: Functions để show/hide modal và submit form
- **CSS**: Sử dụng existing modal classes từ style.css

### 6. Button Update

- **Cập nhật button "Báo hỏng"**: Thêm onclick để mở modal với thông tin phôi cụ thể

## Cách sử dụng:

1. Truy cập trang danh sách phôi văn bằng của một import
2. Với phôi có trạng thái cho phép báo hỏng (không phải DAMAGED), click button "Báo hỏng"
3. Modal sẽ mở với:
    - Số sê-ri phôi (readonly)
    - Dropdown lý do hỏng (required) - lấy từ bảng damage_reasons
    - Textarea mô tả chi tiết (tùy chọn)
4. Chọn lý do và nhập mô tả (nếu cần)
5. Click "Báo hỏng" để xác nhận
6. Phôi sẽ được cập nhật trạng thái thành DAMAGED với thông tin hỏng

## Tính năng bảo mật:

- Validation form input
- Kiểm tra permission báo hỏng theo trạng thái
- CSRF protection
- Foreign key constraints
- Logging hoạt động

## Demo ready!

Server đang chạy tại: http://127.0.0.1:8000
