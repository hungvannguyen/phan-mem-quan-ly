# Demo chức năng cập nhật chuyên ngành trong Add Degree Modal

## Tính năng đã cập nhật:

### ✅ 1. Dropdown chuyên ngành thay thế input text

- **Trước**: Input text tự do nhập tên chuyên ngành
- **Sau**: Dropdown select từ bảng Major với danh sách có sẵn

### ✅ 2. Mặc định chọn chuyên ngành của sinh viên

- Tự động chọn chuyên ngành mà sinh viên đang học
- Có thể thay đổi sang chuyên ngành khác nếu cần

### ✅ 3. Cập nhật database và model

- Thêm trường `major_id` vào bảng `degrees`
- Tạo relationship `major()` trong Degree model
- Cập nhật fillable fields và validation

### ✅ 4. Hiển thị chuyên ngành trong danh sách degrees

- Hiển thị tên chuyên ngành từ relationship `major`
- Fallback về `major_name` nếu không có relationship
- Eager loading để tối ưu performance

## Cấu trúc code:

### Modal HTML:

```blade
<select name="major_id" id="major_id" class="field-input">
    <option value="">Chọn chuyên ngành</option>
    @foreach($majors as $major)
        <option value="{{ $major->major_id }}"
            {{ $student->major_id == $major->major_id ? 'selected' : '' }}>
            {{ $major->major_name }}
        </option>
    @endforeach
</select>
```

### Controller Logic:

```php
// Validation
'major_id' => 'nullable|exists:majors,major_id',

// Auto-fill major_name from Major model
if (!empty($validated['major_id'])) {
    $major = Major::find($validated['major_id']);
    if ($major) {
        $validated['major_name'] = $major->major_name;
    }
}
```

### Database Migration:

```php
$table->unsignedBigInteger('major_id')->nullable()->after('decision_number');
$table->foreign('major_id')->references('major_id')->on('majors')->onDelete('set null');
```

## Cách test:

1. Truy cập trang chỉnh sửa sinh viên: `/student/{id}`
2. Click button "Thêm văn bằng mới"
3. Kiểm tra dropdown "Chuyên ngành":
    - ✅ Hiển thị danh sách từ bảng Major
    - ✅ Mặc định chọn chuyên ngành của sinh viên
    - ✅ Có thể thay đổi sang chuyên ngành khác
4. Điền thông tin và lưu
5. Kiểm tra văn bằng vừa tạo:
    - ✅ Hiển thị tên chuyên ngành trong danh sách

## Kết quả:

- ✅ Dropdown chuyên ngành từ database Major
- ✅ Tự động chọn chuyên ngành hiện tại của sinh viên
- ✅ Có thể thay đổi chuyên ngành khi cần
- ✅ Lưu cả major_id và major_name để tương thích ngược
- ✅ Hiển thị chuyên ngành trong danh sách degrees

🎉 **Tính năng hoàn thành! Modal giờ sử dụng dropdown Major với default value từ sinh viên.**
