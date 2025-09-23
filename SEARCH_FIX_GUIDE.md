# Hướng dẫn sửa lỗi tìm kiếm và thêm tính năng degree_type

## Các bước thực hiện:

### 1. Chạy Migration để thêm cột degree_type

```bash
# Di chuyển đến thư mục project
cd /Users/hung/Documents/Project/phan-mem-quan-ly

# Chạy migration để thêm cột degree_type vào bảng degrees
php artisan migrate

# Kiểm tra migration đã chạy thành công
php artisan migrate:status
```

### 2. Cập nhật dữ liệu mẫu (nếu có dữ liệu hiện tại)

```sql
-- Cập nhật các bản ghi hiện có với degree_type mặc định
UPDATE degrees SET degree_type = 'bachelor' WHERE degree_type IS NULL;
```

### 3. Test tìm kiếm

#### Test các trường hợp tìm kiếm:

1. **Chỉ nhập tên**: Không bị lỗi, tìm được kết quả
2. **Chỉ nhập mã sinh viên**: Tìm được kết quả
3. **Chỉ chọn ngành**: Tìm được kết quả
4. **Kết hợp nhiều trường**: Tìm được kết quả intersection
5. **Tìm theo degree_type**: Cần có dữ liệu degrees với degree_type

## Các thay đổi đã thực hiện:

### 1. Controller (DiplomaManagementController.php)

- ✅ Thêm điều kiện `$request->filled()` cho tất cả search fields
- ✅ Mỗi filter hoạt động độc lập, không bắt buộc phải nhập tất cả
- ✅ Kích hoạt lại tìm kiếm theo degree_type

### 2. Model (Degree.php)

- ✅ Thêm 'degree_type' vào $fillable
- ✅ Thêm casting cho degree_type

### 3. Migration

- ✅ Tạo migration thêm cột degree_type với enum values
- ✅ Default value là 'bachelor'

### 4. View (diploma-management.blade.php)

- ✅ Kích hoạt lại form field degree_type

## Các loại degree_type hỗ trợ:

- `bachelor`: Cử nhân
- `master`: Thạc sĩ
- `doctor`: Tiến sĩ
- `certificate`: Chứng chỉ

## Lưu ý:

- Tìm kiếm giờ đây linh hoạt: có thể nhập 1 trường bất kỳ
- Không bắt buộc phải nhập tất cả các trường
- Các filter kết hợp với nhau bằng AND logic
- degree_type filter chỉ hoạt động khi sinh viên có degrees record

## Cách test sau khi chạy migration:

1. Thử tìm kiếm chỉ với tên: "Ol" → Không lỗi
2. Thử tìm kiếm chỉ với ngành → Không lỗi
3. Thử kết hợp tên + ngành → Kết quả intersection
4. Thử với degree_type (cần tạo dữ liệu trước)
