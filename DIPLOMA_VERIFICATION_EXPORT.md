# Xuất Công Văn Xác Minh Văn Bằng

## Tổng Quan

Tính năng xuất công văn xác minh văn bằng cho phép xuất file Word (.docx) dựa trên mẫu có sẵn để xác nhận thông tin văn bằng của sinh viên.

## Cách Sử Dụng

1. **Từ Trang Quản Lý Sinh Viên:**

    - Truy cập trang "Quản lý Sinh viên và Văn bằng"
    - Tìm sinh viên cần xuất công văn xác minh
    - Nhấn nút **"Xác minh"** (màu xanh cyan) ở cột "Hành động"
    - File Word sẽ được tải xuống tự động

2. **Điều Kiện:**
    - Sinh viên phải có ít nhất 1 văn bằng đã được cấp
    - Nếu chưa có văn bằng, nút "Xác minh" sẽ không hiển thị

## File Template

**Vị trí:** `/public/excel-template/[Mau XM01] Cong van tra loi xac minh van bang.docx`

File template này sử dụng các placeholder (biến thay thế) được đánh dấu bằng cú pháp `${tên_biến}`.

## Danh Sách Placeholder Có Sẵn

### Thông Tin Sinh Viên

- `${student_code}` - Mã số sinh viên
- `${full_name}` - Họ và tên sinh viên
- `${date_of_birth}` - Ngày sinh (định dạng: dd/mm/yyyy)
- `${place_of_birth}` - Nơi sinh
- `${gender}` - Giới tính (Nam/Nữ)
- `${nation}` - Dân tộc
- `${nationality}` - Quốc tịch
- `${class_name}` - Tên lớp học

### Thông Tin Ngành Học

- `${major_name}` - Tên ngành đào tạo
- `${major_code}` - Mã ngành đào tạo

### Thông Tin Văn Bằng

- `${degree_type}` - Loại văn bằng (Cử nhân, Thạc sĩ, Tiến sĩ, Chứng chỉ)
- `${registration_number}` - Số hiệu văn bằng
- `${diploma_number}` - Số văn bằng (giống registration_number)
- `${granting_date}` - Ngày cấp (định dạng: dd/mm/yyyy)
- `${graduation_year}` - Năm tốt nghiệp
- `${ranking}` - Xếp loại tốt nghiệp
- `${decision_number}` - Số quyết định
- `${serial_number}` - Số seri phôi văn bằng
- `${diploma_blank_type}` - Loại phôi văn bằng
- `${number_in_the_book}` - Số vào sổ gốc

### Ngày Tháng Hiện Tại

- `${current_date}` - Ngày hiện tại (định dạng: dd/mm/yyyy)
- `${current_day}` - Ngày hiện tại (số)
- `${current_month}` - Tháng hiện tại (số)
- `${current_year}` - Năm hiện tại (số)

## Ví Dụ Sử Dụng Trong Template

```
Kính gửi: ...

Văn bằng số: ${registration_number}
Họ và tên: ${full_name}
Ngày sinh: ${date_of_birth}
Nơi sinh: ${place_of_birth}
Ngành đào tạo: ${major_name} (${major_code})
Loại văn bằng: ${degree_type}
Ngày cấp: ${granting_date}
Xếp loại: ${ranking}

Ngày ${current_day} tháng ${current_month} năm ${current_year}
```

## Cấu Trúc Kỹ Thuật

### File Export Class

**Vị trí:** `app/Exports/DiplomaVerificationExport.php`

Class này xử lý:

- Load file template Word
- Thay thế các placeholder bằng dữ liệu thực
- Tạo file mới và lưu vào thư mục tạm
- Trả về file để download

### Controller Method

**Vị trí:** `app/Http/Controllers/DiplomaManagementController.php`
**Method:** `exportDiplomaVerification(Student $student)`

### Route

**URL:** `/student/{student_id}/export-verification`
**Method:** GET
**Permission:** `diplomas.view`

### View Component

**Vị trí:** `resources/views/components/students/table.blade.php`
**Button:** Nút "Xác minh" (btn-info)

## Lưu Ý

1. **Multiple Degrees:** Hiện tại, nếu sinh viên có nhiều văn bằng, hệ thống sẽ xuất thông tin văn bằng đầu tiên. Có thể mở rộng để cho phép chọn văn bằng cụ thể.

2. **Template Format:** File template phải là định dạng .docx (Microsoft Word 2007+). Không hỗ trợ .doc cũ.

3. **Placeholder Syntax:** Sử dụng cú pháp `${tên_biến}` trong template. Chú ý không có khoảng trắng.

4. **Missing Data:** Nếu dữ liệu không có (null), placeholder sẽ được thay thế bằng chuỗi rỗng.

5. **Temporary Files:** File được tạo trong `storage/app/temp/` và sẽ tự động xóa sau khi download.

## Troubleshooting

### Lỗi: "Template file not found"

- Kiểm tra file template có tồn tại tại `/public/excel-template/[Mau XM01] Cong van tra loi xac minh van bang.docx`
- Kiểm tra quyền đọc file

### Lỗi: "Sinh viên chưa được cấp văn bằng"

- Sinh viên cần có ít nhất 1 văn bằng đã được cấp
- Kiểm tra bảng `degrees` trong database

### File Word bị lỗi khi mở

- Kiểm tra cú pháp placeholder trong template
- Đảm bảo template gốc không bị corrupt
- Kiểm tra thư viện PHPWord đã được cài đặt: `composer require phpoffice/phpword`

## Package Dependencies

- **PHPWord:** `phpoffice/phpword` ^1.4
- Đã được cài đặt qua Composer

## Tương Lai

Có thể mở rộng thêm:

1. Cho phép chọn văn bằng cụ thể (nếu sinh viên có nhiều văn bằng)
2. Thêm template khác cho các loại xác nhận khác
3. Export hàng loạt cho nhiều sinh viên
4. Tùy chỉnh template trực tiếp từ giao diện admin
