# Degree Reissue Seeder Documentation

## Tổng quan

`DegreeReissueSeeder` tạo dữ liệu mẫu cho bảng `degree_reissues` - lưu trữ lịch sử cấp lại văn bằng trong hệ thống.

## File Location

```
database/seeders/DegreeReissueSeeder.php
```

## Chức năng

Seeder này sẽ:

1. **Chọn ngẫu nhiên 15 văn bằng** từ database hiện có
2. **Tạo 1-3 lần cấp lại** cho mỗi văn bằng (ngẫu nhiên)
3. **Sinh dữ liệu thực tế** bao gồm:
    - Số hiệu văn bằng cũ và mới
    - Nội dung chỉnh sửa (lý do cấp lại)
    - Số quyết định thu hồi và cấp lại
    - Ngày quyết định
    - Ghi chú bổ sung (50% chance)
4. **Cập nhật số hiệu văn bằng** trong bảng degrees thành số hiệu mới nhất

## Dữ liệu mẫu

### Lý do cấp lại (Edit Reasons)

Seeder sử dụng các lý do thực tế:

- Sửa lỗi chính tả trong họ và tên
- Điều chỉnh ngày sinh theo giấy khai sinh
- Cập nhật tên ngành đào tạo mới
- Sửa lỗi in ấn trên văn bằng
- Thay đổi xếp loại tốt nghiệp theo quyết định mới
- Điều chỉnh năm tốt nghiệp
- Bổ sung thông tin chuyên ngành
- Sửa lỗi số hiệu văn bằng
- Cập nhật theo quyết định hội đồng
- Thay thế văn bằng bị hư hỏng
- Cấp lại do mất văn bằng gốc
- Điều chỉnh thông tin theo hồ sơ

### Số quyết định (Decision Number)

Format: `{Prefix}-{Year}-{Number}`

Ví dụ:

- `QĐ-HVANND-CL-2025-001`
- `QĐ-BGH-CL-2024-123`
- `QĐ-ĐHQG-CL-2026-045`

Prefix có thể là:

- `QĐ-HVANND-CL` (Quyết định Hiệu trưởng - Cấp lại)
- `QĐ-BGH-CL` (Quyết định Ban Giám hiệu - Cấp lại)
- `QĐ-ĐHQG-CL` (Quyết định ĐHQG - Cấp lại)

### Số hiệu văn bằng mới

Logic tạo số hiệu mới:

```php
$prefix = substr($currentRegNumber, 0, 2);    // Giữ nguyên prefix (VD: CN, KS, TS)
$year = substr($currentRegNumber, 2, 4);      // Giữ nguyên năm
$number = (int)substr($currentRegNumber, 6);  // Lấy số hiện tại
$newNumber = $number + ($i + 1) * 1000;       // Tăng 1000, 2000, 3000...
$newRegNumber = $prefix . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
```

Ví dụ:

- Cũ: `CN2025000123` → Mới: `CN2025001123` (lần 1)
- Mới: `CN2025001123` → Mới tiếp: `CN2025002123` (lần 2)

## Cách chạy

### Chạy riêng seeder này

```bash
php artisan db:seed --class=DegreeReissueSeeder
```

### Chạy toàn bộ seeders (bao gồm cả DegreeReissueSeeder)

```bash
php artisan db:seed
```

hoặc

```bash
php artisan migrate:fresh --seed
```

## Integration với DevelopmentSeeder

DegreeReissueSeeder được gọi tự động trong `DevelopmentSeeder`:

```php
public function run(): void
{
    DB::transaction(function () {
        // ... other seeders
    });

    // 10. Create degree adjustments
    $this->createDegreeAdjustments();

    // 11. Student Updates
    $this->call(StudentUpdateSeeder::class);

    // 12. Degree Reissues ← Mới thêm
    $this->call(DegreeReissueSeeder::class);
}
```

**Quan trọng**: Seeder này phải chạy sau khi đã có degrees trong database.

## Kết quả mong đợi

Sau khi chạy seeder:

- ✅ Tạo khoảng **30-45 records** trong bảng `degree_reissues`
- ✅ **15 văn bằng** được chọn ngẫu nhiên sẽ có lịch sử cấp lại
- ✅ Mỗi văn bằng có **1-3 lần cấp lại** (random)
- ✅ Số hiệu văn bằng trong bảng `degrees` được **cập nhật** thành số mới nhất
- ✅ Ngày quyết định trong khoảng **1-24 tháng** gần đây

## Kiểm tra dữ liệu

### Kiểm tra tổng số lịch sử cấp lại

```bash
php artisan tinker
>>> \App\Models\DegreeReissue::count();
```

### Xem chi tiết một số lịch sử

```bash
php artisan tinker
>>> \App\Models\DegreeReissue::with('degree.student')->limit(5)->get();
```

### Kiểm tra văn bằng đã được cập nhật số hiệu

```bash
php artisan tinker
>>> $reissue = \App\Models\DegreeReissue::first();
>>> echo "Cũ: {$reissue->old_registration_number}\n";
>>> echo "Mới: {$reissue->new_registration_number}\n";
>>> echo "Hiện tại: {$reissue->degree->registration_number}\n";
```

Kết quả mong đợi: Số hiệu hiện tại phải trùng với số hiệu mới nhất trong lịch sử cấp lại.

## Dependencies

Seeder này phụ thuộc vào:

1. **Degrees table** phải có dữ liệu
2. **Students table** phải có dữ liệu
3. **DegreeReissue model** phải được định nghĩa đúng
4. **Relationships** giữa Degree và DegreeReissue phải hoạt động

## Lưu ý quan trọng

### 1. Transaction Safety

Seeder chạy trong transaction để đảm bảo tính toàn vẹn dữ liệu:

- Nếu có lỗi, toàn bộ dữ liệu sẽ rollback
- Không có partial data

### 2. Random Data

Mỗi lần chạy seeder sẽ tạo dữ liệu khác nhau do:

- Chọn degrees ngẫu nhiên
- Số lần cấp lại ngẫu nhiên (1-3)
- Lý do cấp lại ngẫu nhiên
- Ngày quyết định ngẫu nhiên

### 3. Timestamps

- `created_at` và `updated_at` được set dựa trên `decision_date`
- Thêm 1-7 ngày sau ngày quyết định để giả lập quá trình xử lý

### 4. Soft Deletes

Model sử dụng soft deletes - dữ liệu không bị xóa vĩnh viễn:

```php
use Illuminate\Database\Eloquent\SoftDeletes;
```

## Troubleshooting

### Error: "Integrity constraint violation"

**Nguyên nhân**: Không có degrees trong database

**Giải pháp**:

```bash
php artisan db:seed --class=DevelopmentSeeder  # Tạo toàn bộ dữ liệu mẫu
# hoặc
php artisan migrate:fresh --seed  # Reset toàn bộ và seed lại
```

### Error: "Class 'DegreeReissue' not found"

**Nguyên nhân**: Model chưa được tạo hoặc autoload chưa cập nhật

**Giải pháp**:

```bash
composer dump-autoload
```

### Không có dữ liệu được tạo

**Nguyên nhân**: Không có đủ degrees để chọn

**Giải pháp**: Kiểm tra số lượng degrees:

```bash
php artisan tinker
>>> \App\Models\Degree::count();
```

Nếu < 15, chạy:

```bash
php artisan db:seed --class=DevelopmentSeeder
```

## Testing trong Development

Để test seeder nhiều lần:

```bash
# Xóa dữ liệu cũ
php artisan tinker
>>> \App\Models\DegreeReissue::query()->forceDelete();
>>> exit

# Chạy lại seeder
php artisan db:seed --class=DegreeReissueSeeder
```

## Production Notes

⚠️ **Không chạy seeder này trong production!**

Seeder này chỉ dành cho development/testing. Trong production:

- Dữ liệu cấp lại văn bằng phải được nhập thực tế qua UI
- Sử dụng form trong trang edit sinh viên
- Có validation và logging đầy đủ

## Summary

```
┌─────────────────────────────────────────────────────────┐
│  DegreeReissueSeeder                                    │
├─────────────────────────────────────────────────────────┤
│  Input:  15 degrees (random)                            │
│  Output: 30-45 reissue records                          │
│  Time:   1-24 months ago                                │
│  Update: registration_number in degrees                 │
└─────────────────────────────────────────────────────────┘
```
