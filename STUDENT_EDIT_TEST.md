# Test Guide cho Student Edit Page

## Các bước test:

### 1. Test chức năng Sửa sinh viên

1. Mở trang diploma-management
2. Click vào nút "Sửa" (icon edit) của một sinh viên bất kỳ
3. Kiểm tra có chuyển đến trang student-edit không
4. Kiểm tra thông tin sinh viên có hiển thị đúng không

### 2. Test hiển thị thông tin

- ✅ Mã sinh viên hiển thị đúng
- ✅ Tên sinh viên hiển thị đúng
- ✅ Ngày sinh hiển thị đúng format
- ✅ Lớp học hiển thị (nếu có)
- ✅ Ngành đào tạo được chọn đúng

### 3. Test phần Văn bằng

- ✅ Hiển thị số lượng văn bằng đã cấp
- ✅ Nếu có văn bằng: hiển thị chi tiết từng văn bằng
- ✅ Nếu chưa có: hiển thị empty state đẹp

### 4. Test chức năng cập nhật

1. Thay đổi thông tin trong form
2. Click "Cập nhật thông tin"
3. Kiểm tra có lưu thành công và redirect về trang chính không
4. Kiểm tra thông báo success có hiển thị không

### 5. Test validation

1. Xóa trống các trường required (mã sv, tên, ngày sinh, ngành)
2. Submit form
3. Kiểm tra có validation error không

### 6. Test responsive

- Desktop: 3 cột grid
- Tablet: 2 cột grid
- Mobile: 1 cột grid

## Các tính năng đã implement:

### ✅ Giao diện

- Header với tiêu đề và nút quay lại
- Section thông tin sinh viên (form)
- Section văn bằng đã cấp (read-only)
- Action buttons (Hủy/Cập nhật)
- Flash messages (success/error)

### ✅ Backend

- Controller lấy đầy đủ data (student + degrees + majors)
- Form validation phù hợp với DB structure
- Update method hoạt động với fields hiện tại

### ✅ UX/UI

- Responsive design
- Loading states
- Form validation
- Success/error feedback
- Accessible labels và navigation

## Lưu ý:

- Route đã có sẵn: `/student/{student:student_id}`
- Method: `DiplomaManagementController@student`
- View: `student-edit.blade.php`
- Request validation: `StudentRequest`

## Nếu gặp lỗi có thể do:

1. Chưa chạy migration degree_type
2. CSS classes chưa compile
3. Relationship trong model chưa đúng
4. Route parameter không match
