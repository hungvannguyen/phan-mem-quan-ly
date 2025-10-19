# Demo tự động gán phôi cũ nhất trong Add Degree Modal

## Thay đổi từ Manual Selection → Auto Assignment

### ❌ **Trước đây (Manual Selection)**:

- User chọn loại phôi → Load danh sách 10 phôi
- User phải chọn thủ công phôi từ dropdown
- Risk: User có thể chọn phôi mới thay vì cũ
- UX: Thêm step không cần thiết

### ✅ **Bây giờ (Auto Assignment)**:

- User chọn loại phôi → Tự động gán phôi cũ nhất
- Hiển thị thông tin phôi được chọn
- Logic: Luôn lấy phôi cũ nhất khả dụng (FIFO)
- UX: Streamlined, không cần decision making

## Logic tự động gán phôi:

### Backend Algorithm:

```php
// Get oldest available diploma blank
$oldestBlank = DiplomaBlank::where('type_id', $typeId)
    ->where('status', DiplomaBlankStatus::IN_STOCK)
    ->whereDoesntHave('degree') // Not assigned
    ->orderBy('import_date', 'asc') // Oldest by import date
    ->orderBy('serial_number', 'asc') // Then by serial
    ->first();
```

### Tiêu chí ưu tiên:

1. **Import date cũ nhất** (FIFO principle)
2. **Serial number nhỏ nhất** (nếu cùng ngày nhập)
3. **Status = IN_STOCK** (khả dụng)
4. **Chưa được cấp** (`whereDoesntHave('degree')`)

## Frontend UI Changes:

### Trước:

```html
<select name="diploma_blank_id" required>
    <option>Chọn phôi văn bằng</option>
    <option value="1">A001 (Ngày nhập: 01/01/2024)</option>
    <option value="2">A002 (Ngày nhập: 02/01/2024)</option>
</select>
```

### Sau:

```html
<div class="selected-blank-display bg-gray-50">
    <strong class="text-green-600">A001</strong>
    <span class="text-gray-500">Ngày nhập: 01/01/2024</span>
    <i class="fas fa-check-circle text-green-500"></i>
</div>
<input type="hidden" name="diploma_blank_id" value="1" />
```

## Workflow mới:

1. **User chọn "Loại phôi văn bằng"**
2. **System tự động**:
    - Query phôi cũ nhất khả dụng
    - Hiển thị thông tin phôi được chọn
    - Set hidden input value
    - Show confirmation message
3. **User tiếp tục** điền thông tin khác
4. **Submit** → System sử dụng phôi đã được pre-selected

## Benefits:

### 🎯 **Business Logic**

- **FIFO compliance**: Luôn sử dụng phôi cũ trước
- **Inventory management**: Tránh phôi cũ bị tồn kho
- **Consistency**: Cùng 1 logic cho tất cả users

### ⚡ **Performance**

- **Reduced queries**: Chỉ query 1 phôi thay vì 10
- **Faster UX**: Ít clicks, ít decisions
- **Less data transfer**: Không load danh sách dài

### 🔒 **Risk Reduction**

- **No wrong choice**: Không thể chọn sai phôi
- **Audit compliance**: Clear paper trail
- **Predictable behavior**: Luôn biết phôi nào sẽ được dùng

## Error Handling:

### Không có phôi khả dụng:

```json
{
    "success": false,
    "message": "Không có phôi khả dụng cho loại này"
}
```

### UI hiển thị:

```html
<div class="text-red-600">
    <i class="fas fa-exclamation-triangle"></i>
    Không có phôi khả dụng cho loại này
</div>
```

## Testing scenarios:

1. **Happy path**: Có phôi → Auto select oldest
2. **No blanks**: Không có phôi → Error message
3. **Network error**: API fail → Retry với user feedback
4. **Type change**: Đổi loại phôi → Update selection
5. **Modal reset**: Đóng mở lại → Clean state

## Cách test:

1. **Truy cập**: http://127.0.0.1:8000/student/{id}
2. **Click**: "Thêm văn bằng mới"
3. **Chọn**: "Loại văn bằng" (bất kỳ)
4. **Chọn**: "Loại phôi văn bằng"
5. **Kiểm tra**: Phôi tự động hiển thị với:
    - ✅ Serial number (green, bold)
    - ✅ Import date
    - ✅ Check icon
    - ✅ Confirmation message
6. **Submit**: Thành công với phôi đã chọn tự động

🎉 **Tính năng tự động gán phôi cũ nhất hoàn thành! Business logic FIFO được đảm bảo.**
