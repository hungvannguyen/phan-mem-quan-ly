# Demo chức năng chọn phôi văn bằng trong Add Degree Modal

## Tính năng mới đã thêm:

### ✅ 1. Chọn loại phôi văn bằng

- Dropdown hiển thị các loại phôi từ bảng `diploma_blank_types`
- Kích hoạt khi chọn sẽ load danh sách phôi khả dụng

### ✅ 2. Chọn phôi văn bằng cụ thể

- Load dynamically dựa trên loại phôi đã chọn
- Chỉ hiển thị phôi khả dụng:
    - ✅ Status = IN_STOCK
    - ✅ Chưa được cấp cho ai (không có degree)
    - ✅ Không bị hư hỏng
- Hiển thị số serial và ngày nhập

### ✅ 3. Kiểm tra tính khả dụng real-time

- API endpoint: `/api/diploma-blanks/available/{typeId}`
- Trả về tối đa 10 phôi đầu tiên sắp xếp theo serial
- Hiển thị số lượng phôi khả dụng

### ✅ 4. Race condition protection

- Database transaction với `lockForUpdate()`
- Kiểm tra lại trạng thái phôi trước khi tạo degree
- Atomic update status phôi thành ISSUED

### ✅ 5. Cập nhật trạng thái phôi tự động

- Status: IN_STOCK → ISSUED
- Ghi nhận ngày cấp và lý do cấp
- Liên kết với degree vừa tạo

## Cấu trúc code:

### Frontend (Modal):

```blade
<!-- Chọn loại phôi -->
<select name="diploma_blank_type_id" onchange="loadAvailableDiplomaBlanks()">
    @foreach($diplomaBlankTypes as $type)
        <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
    @endforeach
</select>

<!-- Chọn phôi cụ thể -->
<select name="diploma_blank_id" id="diploma_blank_id" required disabled>
    <option value="">Chọn loại phôi trước</option>
</select>
```

### API Endpoint:

```php
public function getAvailableDiplomaBlanks($typeId) {
    $blanks = DiplomaBlank::where('type_id', $typeId)
        ->where('status', DiplomaBlankStatus::IN_STOCK)
        ->whereDoesntHave('degree')
        ->orderBy('serial_number')
        ->limit(10)
        ->get();
}
```

### Transaction Protection:

```php
return DB::transaction(function() use ($validated) {
    // Check and lock diploma blank
    $diplomaBlank = DiplomaBlank::where('diploma_blank_id', $validated['diploma_blank_id'])
        ->where('status', DiplomaBlankStatus::IN_STOCK)
        ->whereDoesntHave('degree')
        ->lockForUpdate()
        ->first();

    if (!$diplomaBlank) {
        throw new Exception('Phôi không khả dụng!');
    }

    // Create degree and update blank status
    $degree = Degree::create($validated);
    $diplomaBlank->update(['status' => DiplomaBlankStatus::ISSUED]);
});
```

## Cách test:

1. **Truy cập trang sinh viên**: `/student/{id}`
2. **Click "Thêm văn bằng mới"**
3. **Test flow chọn phôi**:
    - ✅ Chọn "Loại văn bằng" trước
    - ✅ Chọn "Loại phôi văn bằng" → Load danh sách phôi
    - ✅ Chọn phôi cụ thể từ dropdown
    - ✅ Kiểm tra thông tin hiển thị
4. **Test validation**:
    - ✅ Không thể submit without phôi
    - ✅ Phôi bị lock khi đang process
    - ✅ Message lỗi nếu phôi không khả dụng
5. **Kiểm tra kết quả**:
    - ✅ Degree được tạo thành công
    - ✅ Phôi chuyển status ISSUED
    - ✅ Ghi nhận thông tin issue

## Lợi ích:

### 🔒 **Bảo mật & Tính toàn vẹn dữ liệu**

- Race condition protection
- Atomic operations với database transaction
- Validation đầy đủ trước khi commit

### ⚡ **Performance**

- Limit 10 phôi đầu tiên để tránh load quá nhiều
- Eager loading relationships khi cần
- API response tối ưu với chỉ fields cần thiết

### 👤 **User Experience**

- Real-time feedback về số phôi khả dụng
- Disabled state khi chưa chọn loại phôi
- Clear error messages

### 📊 **Truy vết**

- Log đầy đủ lý do cấp phôi
- Timestamp chính xác
- Audit trail cho mọi thao tác

🎉 **Tính năng hoàn thành! Giờ có thể cấp phôi văn bằng một cách an toàn và có kiểm soát.**
