# Hệ Thống Lịch Sử Thay Đổi (Change Logs)

## Tổng quan

Hệ thống change logs đã được tái cấu trúc để lưu lịch sử thay đổi cho **TẤT CẢ** các entity trong hệ thống (Student, Degree, User, v.v.), không chỉ riêng Degree.

### Thay đổi chính:

1. **Bảng mới**: `change_logs` - bảng tổng quát thay thế `degree_adjustments`
2. **View tương thích**: `degree_adjustments` (view) - đảm bảo code cũ vẫn hoạt động
3. **Model mới**: `ChangeLog` - model chính để làm việc với logs
4. **Trait**: `LogsChanges` - tự động ghi log khi có thay đổi
5. **Model cập nhật**: `Student`, `DegreeAdjustment` đã được cập nhật

## Cấu trúc bảng `change_logs`

```sql
- log_id (PK)
- entity_type (VD: 'Student', 'Degree')
- entity_id (ID của entity)
- changed_field (trường được thay đổi)
- old_value (giá trị cũ)
- new_value (giá trị mới)
- change_description (mô tả chi tiết)
- decision_number (số quyết định nếu có)
- decision_date (ngày quyết định)
- changed_by (người thực hiện)
- action_type (create, update, delete, restore)
- ip_address, user_agent (thông tin bổ sung)
- additional_data (JSON - dữ liệu mở rộng)
- timestamps
```

## Sử dụng

### 1. Tự động ghi log (Khuyến nghị)

Thêm trait `LogsChanges` vào model:

```php
use App\Models\Traits\LogsChanges;

class Student extends Model
{
    use LogsChanges;

    // Model sẽ tự động ghi log khi:
    // - Tạo mới (create)
    // - Cập nhật (update)
    // - Xóa (delete)
    // - Khôi phục (restore)
}
```

### 2. Ghi log thủ công

```php
use App\Models\ChangeLog;

// Cách 1: Sử dụng helper method
ChangeLog::logChange(
    entityType: 'Student',
    entityId: $student->student_id,
    changeDescription: 'Cập nhật thông tin sinh viên',
    changedField: 'full_name',
    oldValue: 'Nguyễn Văn A',
    newValue: 'Nguyễn Văn B',
    actionType: 'update'
);

// Cách 2: Tạo instance mới
ChangeLog::create([
    'entity_type' => 'Student',
    'entity_id' => $student->student_id,
    'changed_field' => 'status',
    'old_value' => 'Đang học',
    'new_value' => 'Đã tốt nghiệp',
    'change_description' => 'Thay đổi trạng thái sinh viên',
    'changed_by' => auth()->id(),
    'action_type' => 'update',
]);
```

### 3. Lấy lịch sử thay đổi

```php
// Cách 1: Từ entity (nếu model có trait LogsChanges)
$student = Student::find($id);
$history = $student->getChangeHistory();

// Cách 2: Sử dụng relationship
$logs = $student->changeLogs()->with('changedBy')->latest()->get();

// Cách 3: Truy vấn trực tiếp
$logs = ChangeLog::forEntity('Student', $studentId)
    ->with('changedBy')
    ->orderBy('created_at', 'desc')
    ->get();

// Cách 4: Helper method
$logs = ChangeLog::getEntityLogs('Student', $studentId);
```

### 4. Tìm kiếm logs

```php
// Lọc theo action type
$updateLogs = ChangeLog::byAction('update')->get();

// Lọc theo người thực hiện
$userLogs = ChangeLog::byUser($userId)->get();

// Kết hợp nhiều điều kiện
$logs = ChangeLog::forEntity('Student', $studentId)
    ->byAction('update')
    ->whereDate('created_at', '>=', now()->subDays(7))
    ->get();
```

## Tùy chỉnh

### Tùy chỉnh trường không log

Trong model:

```php
class Student extends Model
{
    use LogsChanges;

    // Các trường không cần log
    protected $dontLogFields = [
        'last_login_at',
        'login_count',
        'remember_token'
    ];
}
```

### Tùy chỉnh mô tả log

```php
class Student extends Model
{
    use LogsChanges;

    protected function getUpdateDescription(string $field, $oldValue, $newValue): string
    {
        if ($field === 'status') {
            return "Thay đổi trạng thái từ {$oldValue} sang {$newValue}";
        }
        return parent::getUpdateDescription($field, $oldValue, $newValue);
    }
}
```

### Tùy chỉnh label tiếng Việt

```php
class Student extends Model
{
    use LogsChanges;

    protected $fieldLabels = [
        'student_code' => 'Mã sinh viên',
        'full_name' => 'Họ và tên',
        'status' => 'Trạng thái',
        // ...
    ];
}
```

## Tương thích ngược

### DegreeAdjustment vẫn hoạt động

Code cũ sử dụng `DegreeAdjustment` vẫn hoạt động bình thường nhờ view `degree_adjustments`:

```php
// Code cũ vẫn chạy
$degree = Degree::find($id);
$adjustments = $degree->adjustments; // Vẫn hoạt động!

// Nhưng để tạo mới, nên dùng ChangeLog
ChangeLog::logChange(
    entityType: 'Degree',
    entityId: $degree->degree_id,
    changeDescription: 'Điều chỉnh văn bằng',
    // ...
);
```

## View trong Blade

```blade
{{-- Hiển thị lịch sử thay đổi sinh viên --}}
@php
    $studentChangeLogs = $student->changeLogs()->with('changedBy')->latest()->get();
@endphp

@foreach ($studentChangeLogs as $log)
    <div class="log-item">
        <strong>{{ $log->change_description }}</strong>

        @if($log->old_value && $log->new_value)
            <span class="old">{{ $log->old_value }}</span>
            →
            <span class="new">{{ $log->new_value }}</span>
        @endif

        <small>
            Bởi {{ $log->changedBy->full_name }}
            - {{ $log->created_at->diffForHumans() }}
        </small>
    </div>
@endforeach
```

## Migration

File migration đã gộp tất cả:

- `2024_01_01_000009_create_degree_adjustments_table.php` → Đã đổi thành tạo `change_logs`
- Các file migration add column đã được xóa và gộp vào file chính

Để migrate:

```bash
php artisan migrate:fresh --seed
```

## Lợi ích

1. ✅ **Tổng quát**: Một bảng cho tất cả entity
2. ✅ **Tự động**: Trait tự động ghi log
3. ✅ **Chi tiết**: Lưu cả old/new value
4. ✅ **Tương thích**: Code cũ vẫn chạy
5. ✅ **Mở rộng**: Dễ thêm entity mới
6. ✅ **Audit trail**: Đầy đủ thông tin (IP, User Agent, v.v.)

## Notes

- View `degree_adjustments` chỉ để đọc, không insert/update trực tiếp
- Nên dùng `ChangeLog` cho tất cả thao tác mới
- Trait `LogsChanges` tự động handle create/update/delete/restore
- Có thể thêm `additional_data` (JSON) để lưu metadata tùy chỉnh
