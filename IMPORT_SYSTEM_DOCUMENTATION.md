# Hệ thống Import Dữ liệu

## Tổng quan

Hệ thống import được thiết kế theo kiến trúc "Mỗi cấu trúc một Class" để dễ dàng mở rộng và bảo trì. Mỗi loại dữ liệu có một Import Class riêng biệt.

## Cấu trúc Files

```
app/
├── Imports/                    # Các class xử lý import
│   ├── DegreeImport.php       # Import bằng Cử nhân, Thạc sĩ, Tiến sĩ
│   ├── PoliticalTheoryImport.php  # Import Lý luận chính trị
│   └── CertificateImport.php  # Import Chứng chỉ
├── Http/Controllers/
│   └── DataImportController.php   # Controller điều hướng
├── Models/
│   └── ImportLog.php          # Model lưu logs
├── Jobs/
│   └── ProcessImportJob.php  # Job xử lý queue
└── Traits/
    └── ImportHelper.php       # Trait chứa helper functions
```

## Các loại Import

### 1. DegreeImport

- **Mục đích**: Import thông tin bằng Cử nhân, Thạc sĩ, Tiến sĩ
- **File**: `app/Imports/DegreeImport.php`
- **Cấu trúc Excel**: TODO - Cần định nghĩa

### 2. PoliticalTheoryImport

- **Mục đích**: Import thông tin cấp bằng Lý luận chính trị
- **File**: `app/Imports/PoliticalTheoryImport.php`
- **Cấu trúc Excel**: TODO - Cần định nghĩa

### 3. CertificateImport

- **Mục đích**: Import thông tin cấp chứng chỉ
- **File**: `app/Imports/CertificateImport.php`
- **Cấu trúc Excel**: TODO - Cần định nghĩa

## Features

### ✅ Đã implement

1. **Kiến trúc phân tách**: Mỗi loại dữ liệu có class riêng
2. **Controller thông minh**: Tự động điều hướng đến class phù hợp
3. **Validation**: Rules validation cho từng loại
4. **Batch Processing**: Xử lý theo batch để tối ưu hiệu suất
5. **Chunk Reading**: Đọc file lớn theo chunk
6. **Error Tracking**: Log chi tiết các lỗi
7. **Import Logs**: Lưu lịch sử import vào database
8. **Queue Processing**: Xử lý bất đồng bộ cho file lớn
9. **Helper Trait**: Các hàm xử lý dữ liệu chung

### 🔨 Cần implement

1. **Logic mapping**: Implement logic mapping cụ thể trong mỗi Import class
2. **Validation rules**: Định nghĩa đầy đủ validation rules
3. **Templates**: Tạo file template Excel mẫu
4. **Notifications**: Gửi thông báo khi import hoàn thành
5. **Export errors**: Xuất file Excel chứa các dòng lỗi

## Sử dụng

### 1. Import trực tiếp (Synchronous)

```php
// Trong controller hoặc code
use App\Imports\DegreeImport;
use Maatwebsite\Excel\Facades\Excel;

$import = new DegreeImport();
Excel::import($import, $file);

// Lấy thống kê
$stats = $import->getStatistics();
```

### 2. Import qua Queue (Asynchronous)

```php
use App\Jobs\ProcessImportJob;

ProcessImportJob::dispatch($importLogId, $filePath, $importType);
```

### 3. Qua Web Interface

1. Truy cập `/data-import`
2. Chọn loại dữ liệu
3. Upload file Excel
4. Chọn xử lý bất đồng bộ (tùy chọn)
5. Bấm "Bắt đầu Import"
6. Xem kết quả tại `/data-import/logs`

## Migration

Chạy migration để tạo bảng `import_logs`:

```bash
php artisan migrate
```

## Queue Configuration

Để sử dụng Queue, cần cấu hình:

1. **Cấu hình Queue Driver** trong `.env`:

```env
QUEUE_CONNECTION=database
```

2. **Tạo bảng jobs** (nếu dùng database):

```bash
php artisan queue:table
php artisan migrate
```

3. **Chạy Queue Worker**:

```bash
php artisan queue:work
```

hoặc dùng Supervisor để tự động restart:

```bash
php artisan queue:work --tries=3 --timeout=3600
```

## ImportHelper Trait

Trait chứa các helper functions hữu ích:

- `parseDate()`: Parse ngày từ nhiều format
- `cleanString()`: Trim và clean string
- `parsePhoneNumber()`: Parse số điện thoại
- `parseEmail()`: Parse và validate email
- `removeVietnameseTones()`: Bỏ dấu tiếng Việt
- `parseBoolean()`: Parse boolean từ nhiều format
- `parseNumber()`: Parse số từ string
- `parseIdentityCard()`: Parse CCCD/CMND
- `normalizeMajorName()`: Chuẩn hóa tên ngành
- `validateRequiredFields()`: Validate required fields

## Thêm loại Import mới

1. **Tạo Import Class mới**:

```bash
php artisan make:import YourNewImport
```

2. **Implement logic** trong `YourNewImport.php`:

```php
use App\Traits\ImportHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class YourNewImport implements ToModel, WithHeadingRow
{
    use ImportHelper;

    public function model(array $row)
    {
        // Your logic here
    }

    public function rules(): array
    {
        return [
            // Your validation rules
        ];
    }
}
```

3. **Thêm vào Controller**:

```php
// Trong DataImportController::getImportInstance()
case 'your_new_type':
    return new YourNewImport();
```

4. **Cập nhật enum trong migration** `import_logs`:

```php
$table->enum('import_type', ['degree', 'political_theory', 'certificate', 'your_new_type']);
```

5. **Thêm route và view** nếu cần

## Logs và Monitoring

### Xem logs trong database

```sql
SELECT * FROM import_logs
WHERE status = 'failed'
ORDER BY created_at DESC;
```

### Xem logs trong Laravel

```bash
tail -f storage/logs/laravel.log
```

### Các status của ImportLog

- `processing`: Đang xử lý
- `completed`: Hoàn thành không lỗi
- `completed_with_errors`: Hoàn thành có lỗi
- `failed`: Thất bại hoàn toàn

## Best Practices

1. **Luôn validate** dữ liệu trước khi insert
2. **Sử dụng WithHeadingRow** để tự động mapping theo tên cột
3. **Sử dụng Queue** cho file > 1000 dòng
4. **Log đầy đủ** để dễ debug
5. **Test với data nhỏ** trước khi import lớn
6. **Backup database** trước khi import
7. **Sử dụng Transaction** nếu cần rollback

## Troubleshooting

### Lỗi memory

- Tăng `memory_limit` trong php.ini
- Giảm `batchSize()` và `chunkSize()`
- Sử dụng Queue

### Lỗi timeout

- Tăng `max_execution_time` trong php.ini
- Tăng `timeout` trong Job
- Sử dụng Queue

### Validation errors

- Kiểm tra file template
- Xem chi tiết lỗi trong import logs
- Validate từng dòng trước khi import

## License

MIT
