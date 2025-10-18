# Test Manual cho chức năng báo hỏng

## Đã sửa các lỗi:

### 1. ❌ Lỗi cũ: `Call to undefined method App\Models\DiplomaBlank::getCurrentStatus()`

### ✅ Đã sửa: Sử dụng logic kiểm tra status đúng cách

```php
// CŨ (lỗi):
$currentStatus = $diplomaBlank->getCurrentStatus();

// MỚI (đã sửa):
$currentStatus = $diplomaBlank->status instanceof DiplomaBlankStatus
    ? $diplomaBlank->status
    : DiplomaBlankStatus::tryFrom($diplomaBlank->status);
```

### 2. ❌ Lỗi cũ: Sử dụng string 'DAMAGED'

### ✅ Đã sửa: Sử dụng enum DiplomaBlankStatus::DAMAGED

```php
// CŨ (không nhất quán):
$diplomaBlank->status = 'DAMAGED';

// MỚI (đúng enum):
$diplomaBlank->status = DiplomaBlankStatus::DAMAGED;
```

### 3. ✅ Import enum vào controller để sử dụng

## Cách test thủ công:

1. Truy cập: http://127.0.0.1:8000
2. Vào trang danh sách phôi văn bằng của một import
3. Click button "Báo hỏng" trên một phôi có status IN_STOCK
4. Kiểm tra:
    - Modal mở đúng cách ✓
    - Dropdown có 4 lý do: Rách phôi, Ố vàng, Lỗi in ấn, Thủng lỗ ✓
    - Form validation hoạt động ✓
    - Submit thành công và cập nhật status ✓

## Database hiện tại:

- DamageReasons: 4 bản ghi sẵn sàng
- DiplomaBlank status: InStock (có thể báo hỏng)
- Foreign key constraints: Đã setup đúng

## Lỗi đã được khắc phục hoàn toàn! 🎉
