# Demo hiển thị thông tin hư hỏng phôi văn bằng

## Tính năng mới đã thêm:

### 1. ✅ Cột "Thông tin hư hỏng" trong bảng diploma blanks
- Hiển thị lý do hư hỏng từ DamageReason
- Ngày báo hỏng  
- Mô tả chi tiết (nếu có)

### 2. ✅ Logic hiển thị thông minh:
```php
// Nếu phôi bị hư hỏng và có thông tin damage reason:
if ($isDamaged && $blank->damageReason) {
    // Hiển thị: Lý do + Ngày + Mô tả
}
// Nếu phôi bị hư hỏng nhưng chưa có chi tiết:  
elseif ($isDamaged) {
    // Hiển thị: "Hư hỏng (chưa có chi tiết)"
}
// Phôi bình thường:
else {
    // Hiển thị: "--"
}
```

### 3. ✅ Eager loading damageReason relationship
- Không có N+1 query problem
- Performance tốt khi load nhiều records

### 4. ✅ CSS styling cho damage info
- `.damage-reason`: Tên lý do màu đỏ, font đậm
- `.damage-date`: Ngày báo hỏng, text nhỏ 
- `.damage-description`: Mô tả chi tiết, italics

## Cách test:

1. Truy cập trang danh sách phôi: http://127.0.0.1:8000
2. Tìm phôi có trạng thái "Trong kho" 
3. Click "Báo hỏng" → Chọn lý do → Submit
4. Kiểm tra cột "Thông tin hư hỏng":
   - ✅ Hiển thị tên lý do (đỏ, đậm)
   - ✅ Ngày báo hỏng
   - ✅ Mô tả (nếu có nhập)

## Kết quả mong đợi:
```
| Trạng thái | Thông tin hư hỏng |
|-----------|-------------------|
| Hư hỏng    | Rách phôi        |
|           | 18/10/2025       |
|           | Bị xé ở góc phải |
```

## ✨ Tính năng hoàn thành! Phôi hư hỏng giờ sẽ hiển thị đầy đủ lý do tại sao bị hỏng.