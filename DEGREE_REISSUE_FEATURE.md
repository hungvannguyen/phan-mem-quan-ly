# Tính năng Cấp lại Văn bằng

## Tổng quan

Tính năng "Cấp lại văn bằng" cho phép lưu trữ lịch sử mỗi lần văn bằng được cấp lại cho sinh viên. Mỗi lần cấp lại sẽ được lưu trữ đầy đủ thông tin bao gồm:

- Số hiệu văn bằng cũ
- Số hiệu văn bằng mới
- Nội dung chỉnh sửa (lý do cấp lại)
- Quyết định thu hồi, hủy bỏ và cấp lại
- Ngày quyết định
- Ghi chú bổ sung

## Cấu trúc Database

### Bảng `degree_reissues`

```sql
CREATE TABLE degree_reissues (
    reissue_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    degree_id BIGINT NOT NULL,
    old_registration_number VARCHAR(50) COMMENT 'Số hiệu văn bằng cũ',
    new_registration_number VARCHAR(50) COMMENT 'Số hiệu văn bằng mới',
    edit_content TEXT COMMENT 'Nội dung chỉnh sửa',
    recall_decision VARCHAR(100) COMMENT 'QĐ thu hồi, hủy bỏ và cấp lại',
    decision_date DATE COMMENT 'Ngày quyết định',
    notes TEXT NULL COMMENT 'Ghi chú thêm',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (degree_id) REFERENCES degrees(degree_id) ON DELETE CASCADE
);
```

## Files đã tạo/chỉnh sửa

### 1. Migration

- **File**: `database/migrations/2026_01_10_074932_create_degree_reissues_table.php`
- **Mục đích**: Tạo bảng degree_reissues trong database

### 2. Model

- **File**: `app/Models/DegreeReissue.php`
- **Relationships**:
    - `belongsTo(Degree::class)` - Mối quan hệ với bảng degrees

### 3. Degree Model

- **File**: `app/Models/Degree.php`
- **Thêm**: Relationship `hasMany(DegreeReissue::class)` để lấy tất cả lịch sử cấp lại

### 4. Routes

- **File**: `routes/web.php`
- **Routes mới**:
    - `POST /degrees/{degree}/reissues` - Lưu lịch sử cấp lại mới
    - `DELETE /degrees/reissues/{reissue}` - Xóa lịch sử cấp lại

### 5. Controller

- **File**: `app/Http/Controllers/DiplomaManagementController.php`
- **Methods mới**:
    - `storeReissue(Request $request, Degree $degree)` - Xử lý thêm lịch sử cấp lại
    - `deleteReissue($reissueId)` - Xử lý xóa lịch sử cấp lại

### 6. View

- **File**: `resources/views/components/students/edit.blade.php`
- **Thêm**:
    - Section hiển thị lịch sử cấp lại cho mỗi văn bằng
    - Modal form để thêm lịch sử cấp lại mới
    - JavaScript functions để xử lý mở/đóng modal và xóa lịch sử

## Cách sử dụng

### 1. Xem lịch sử cấp lại

Trong trang chỉnh sửa sinh viên, mỗi văn bằng đã cấp sẽ có một section "Lịch sử cấp lại văn bằng" hiển thị:

- Số lượng lần đã cấp lại
- Danh sách các lần cấp lại với đầy đủ thông tin

### 2. Thêm lịch sử cấp lại mới

1. Click nút "Thêm lịch sử cấp lại" trong section lịch sử cấp lại của văn bằng
2. Modal form sẽ hiện ra với các trường:
    - **Số hiệu văn bằng (hiện tại)**: Tự động điền, không thể sửa
    - **Số hiệu văn bằng mới**: Bắt buộc - Nhập số hiệu mới
    - **Nội dung chỉnh sửa**: Bắt buộc - Mô tả lý do cấp lại
    - **QĐ thu hồi & cấp lại**: Bắt buộc - Số quyết định
    - **Ngày quyết định**: Bắt buộc - Ngày ban hành QĐ
    - **Ghi chú**: Tùy chọn - Ghi chú bổ sung
3. Click "Lưu lịch sử cấp lại"

**Lưu ý quan trọng**: Khi lưu lịch sử cấp lại, hệ thống sẽ tự động:

- Lưu lịch sử vào bảng `degree_reissues`
- Cập nhật `registration_number` của văn bằng thành số hiệu mới

### 3. Xóa lịch sử cấp lại

1. Click icon trash (🗑️) ở góc phải của mỗi lịch sử cấp lại
2. Xác nhận xóa trong dialog
3. Lịch sử sẽ được soft delete

## Luồng dữ liệu

```
1. User click "Thêm lịch sử cấp lại"
   ↓
2. JavaScript mở modal với số hiệu hiện tại
   ↓
3. User nhập thông tin và submit form
   ↓
4. POST /degrees/{degree_id}/reissues
   ↓
5. Controller validate dữ liệu
   ↓
6. Tạo record mới trong degree_reissues
   ↓
7. Cập nhật registration_number trong degrees
   ↓
8. Redirect về trang với thông báo thành công
   ↓
9. Hiển thị lịch sử mới trong danh sách
```

## Validation Rules

```php
[
    'old_registration_number' => 'required|string|max:50',
    'new_registration_number' => 'required|string|max:50',
    'edit_content' => 'required|string',
    'recall_decision' => 'required|string|max:100',
    'decision_date' => 'required|date',
    'notes' => 'nullable|string',
]
```

## Permissions

Tính năng này sử dụng các permissions hiện có:

- **diplomas.edit**: Cần thiết để thêm lịch sử cấp lại
- **diplomas.delete**: Cần thiết để xóa lịch sử cấp lại

## UI/UX Features

1. **Design nhất quán**: Sử dụng style tương tự như lịch sử điều chỉnh
2. **Color coding**:
    - Số hiệu cũ: Màu đỏ với gạch ngang
    - Số hiệu mới: Màu xanh lá
3. **Responsive**: Modal và section hoạt động tốt trên mobile
4. **User feedback**: Thông báo success/error sau mỗi thao tác
5. **Confirmation**: Xác nhận trước khi xóa

## Technical Notes

### Eager Loading

Để tối ưu performance, relationship `reissues` được eager load khi lấy degrees:

```php
$degrees = $student->degrees()
    ->with(['major', 'diplomaBlank.type', 'changeLogs.changedBy', 'reissues'])
    ->get();
```

### Soft Deletes

Model `DegreeReissue` sử dụng soft deletes, cho phép khôi phục nếu cần:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class DegreeReissue extends Model
{
    use HasFactory, SoftDeletes;
}
```

### Cascade Delete

Khi một degree bị xóa, tất cả lịch sử cấp lại liên quan sẽ tự động bị xóa:

```php
$table->foreignId('degree_id')
    ->constrained('degrees', 'degree_id')
    ->onDelete('cascade');
```

## Future Enhancements

Có thể mở rộng thêm các tính năng:

1. **Export lịch sử cấp lại**: Export thành Word/PDF
2. **Filter & Search**: Tìm kiếm theo quyết định, ngày
3. **Notifications**: Thông báo khi có cấp lại mới
4. **Approval workflow**: Yêu cầu phê duyệt trước khi cấp lại
5. **Audit trail**: Tracking chi tiết hơn về người thực hiện

## Testing

Để test tính năng:

1. Truy cập trang chỉnh sửa sinh viên có văn bằng
2. Tìm section "Lịch sử cấp lại văn bằng"
3. Click "Thêm lịch sử cấp lại"
4. Điền đầy đủ thông tin và submit
5. Kiểm tra:
    - Lịch sử mới xuất hiện trong danh sách
    - Số hiệu văn bằng đã được cập nhật
    - Có thể xóa lịch sử

## Troubleshooting

### Lỗi "Table degree_reissues doesn't exist"

- **Nguyên nhân**: Migration chưa chạy
- **Giải pháp**: `php artisan migrate`

### Modal không hiển thị

- **Nguyên nhân**: JavaScript chưa load hoặc có lỗi
- **Giải pháp**: Kiểm tra console browser, refresh trang

### Không update được registration_number

- **Nguyên nhân**: Validation hoặc permission issue
- **Giải pháp**: Kiểm tra logs, đảm bảo user có quyền diplomas.edit

## Changelog

### Version 1.0.0 (2026-01-10)

- ✨ Tính năng cấp lại văn bằng
- ✅ CRUD operations cho lịch sử cấp lại
- 🎨 UI/UX hoàn chỉnh với modal và validation
- 📝 Tự động cập nhật registration_number
- 🗑️ Soft delete cho data recovery
